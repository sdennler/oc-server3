<?php
/***************************************************************************
 * for license information see LICENSE.md
 ***************************************************************************/

/*
 * Database Structure Versioning - update DB structure to current version;
 * used for developer & production system
 *
 * You should normally NOT call this script directly, but via dbupdate.php
 * (or something similar on a production system). This ensures that
 * everything takes place in the right order.
 *
 * See http://wiki.opencaching.de/index.php/Entwicklung/Datenbankversionierung
 * (German) and the comments in this file for further documentation.
 */

if (!isset($opt['rootpath'])) {
    $opt['rootpath'] = __DIR__ . '/../htdocs/';
}
require_once __DIR__ . '/../htdocs/lib2/cli.inc.php';
require_once __DIR__ . '/../htdocs/lib2/search/search.inc.php';

if (!sql_field_exists('cache_attrib', 'gc_id')) {
    die(
        "  ERROR: Database structure too old. You must first do a manual update\n" .
        "  up to commit 467aae4 (March 27, 2013) to enable automatic updates.\n" .
        "  See /sql/db-changes.txt.\n"
    );
    // Do not continue with dbupdate.php, because the current data.sql and
    // maintain.php will not fit either.
}

if (!sql_procedure_exists('sp_touch_cache')) {
    // We need a consistent starting point including triggers & functions, and it's
    // safer not to decide HERE which trigger version to install.
    echo "Triggers / DB functions are not installed (yet) - skipping DB versioning.\n";

    return;
    // continue with dbupdate.php if called from there and let's hope
    // maintain.php matches the installed tables' DB version ...
}

$db_version = max(99, sql_value("SELECT `value` FROM `sysconfig` WHERE `name`='db_version'", 99));

do {
    ++$db_version;
    $dbv_function = 'dbv_' . $db_version;
    if (function_exists($dbv_function)) {
        echo 'applying DB mutation #' . $db_version;
        call_user_func($dbv_function);
        sql(
            "INSERT INTO `sysconfig` (`name`,`value`)
             VALUES ('db_version', '&1')
             ON DUPLICATE KEY UPDATE `value`='&1'",
            $db_version
        );
        echo " - ok.\n";
    } else {
        $db_version = -1;
    }
} while ($db_version > 0);

// Ensure that all tables have the right charset, including added tables:
check_tables_charset($opt['db']['placeholder']['db']);
check_tables_storage_engine($opt['db']['placeholder']['db']);

return;


// Check if the tables' charset is consistent with $opt['charset']['mysql'].
// Do an upgrade from utf8 to utf8mb4 if necessary.
// A downgrade will be denied, because it might lose data.
//
// OKAPI tables upgrade is done by a similar function in OKAPI's update module.

function check_tables_charset($database): void
{
    global $opt;

    # set DB default charset

    $current_db_charset = sql_value(
        "SELECT DEFAULT_CHARACTER_SET_NAME
         FROM INFORMATION_SCHEMA.SCHEMATA
         WHERE SCHEMA_NAME = '&1'",
        $opt['db']['placeholder']['db']
    );
    if ($current_db_charset != $opt['charset']['mysql']) {
        if ($opt['charset']['mysql'] == 'utf8mb4') {
            sql(
                "ALTER DATABASE
                 DEFAULT CHARACTER SET 'utf8mb4'
                 DEFAULT COLLATE 'utf8mb4_general_ci'"
            );
        } else {
            echo 'Warning: cannot migrate database to ' . $opt['charset']['mysql'] . "\n";
        }
    }

    # migrate tables
    $rs = sql(
        "SELECT TABLE_NAME, TABLE_COLLATION
         FROM INFORMATION_SCHEMA.TABLES
         WHERE TABLE_SCHEMA='&1' AND TABLE_NAME NOT LIKE 'okapi_%' AND TABLE_NAME NOT LIKE 'migration_versions'",
        $database
    );

    while ($table = sql_fetch_assoc($rs)) {
        $table_collation = explode('_', $table['TABLE_COLLATION']);
        if ($table_collation[0] != $opt['charset']['mysql']) {
            $migrate = 'table `' . $table['TABLE_NAME'] . '` from charset ' .
                $table_collation[0] . ' to ' . $opt['charset']['mysql'];

            if ($table_collation[0] == 'utf8' && $opt['charset']['mysql'] == 'utf8mb4') {
                echo 'migrating ' . $migrate . "\n";
                $table_collation[0] = $opt['charset']['mysql'];
                sql(
                    "ALTER TABLE `&1`
                     CONVERT TO CHARACTER SET '&2'
                     COLLATE '&3'",
                    $table['TABLE_NAME'],
                    $table_collation[0],
                    implode('_', $table_collation)
                );
            } else {
                echo 'Warning: cannot migrate ' . $migrate . "\n";
            }
        }
    }
    sql_free_result($rs);
}

function check_tables_storage_engine($database): void
{
    # migrate tables
    $rs = sql(
        "SELECT TABLE_NAME
         FROM INFORMATION_SCHEMA.TABLES
         WHERE INFORMATION_SCHEMA.TABLES.ENGINE = 'MyISAM'
         AND TABLE_SCHEMA='&1'
         AND TABLE_NAME NOT LIKE 'okapi_%'
         AND TABLE_NAME NOT LIKE 'nuts_layer'
         AND TABLE_NAME NOT LIKE 'npa_areas'
         AND TABLE_NAME NOT LIKE 'migration_versions'",
        $database
    );

    while ($table = sql_fetch_assoc($rs)) {
        sql(
            'ALTER TABLE `&1`
             ENGINE=InnoDB;',
            $table['TABLE_NAME']
        );
    }

    sql_free_result($rs);
}

// Now and then a maintain.php update should be inserted, because multiple
// mutations may be run in one batch, and future mutations may depend on
// changed triggers, which may not be obvious.
//
// Of course, a trigger update mutation can also be inserted directly before a
// mutation which needs it. (But take care that maintain.php at that point does
// not depend on database changes which will be done by that mutation ...)

function update_triggers(): void
{
    global $opt, $db_version;

    // For the case we re-run an old mutation for some accident, we must make
    // sure that we are not downgrading to an old trigger version (which may be
    // incompatible with the current database structures.
    if (sql_function_exists('dbsvTriggerVersion')) {
        $trigger_version = sql_value('SELECT dbsvTriggerVersion()', 0);
    } else {
        $trigger_version = 0;
    }

    if ($trigger_version < $db_version) {
        $syncfile = $opt['rootpath'] . 'var/cache2/dbsv-running';
        file_put_contents($syncfile, 'dbsv is running');

        system('php ' . $opt['rootpath'] . '../sql/stored-proc/maintain.php --dbsv ' . $db_version . ' --flush');
        // This will also update dbsvTriggerVersion.

        if (file_exists($syncfile)) {
            die("\nmaintain.php was not properly executed\n");
            unlink($syncfile);
        }
    }
}


// Database mutations
// - must be consecutively numbered
// - should behave well if run multiple times

/***** OC release 3.0.8 *****/

function dbv_100(): void  // expands log date to datetime, to enable time logging
{
    if (sql_field_type('cache_logs', 'date') != 'DATETIME') {
        sql('ALTER TABLE `cache_logs` CHANGE COLUMN `date` `date` DATETIME NOT NULL');
    }
    if (sql_field_type('cache_logs_archived', 'date') != 'DATETIME') {
        sql('ALTER TABLE `cache_logs_archived` CHANGE COLUMN `date` `date` DATETIME NOT NULL');
    }
}

function dbv_101(): void  // add fields for fixing OKAPI issue #232
{
    if (!sql_field_exists('caches', 'meta_last_modified')) {
        // initialize with '0000-00-00 00:00:00' for existing data, that's ok
        sql(
            "ALTER TABLE `caches` ADD COLUMN `meta_last_modified` DATETIME NOT NULL COMMENT 'via Trigger (cache_logs)' AFTER `listing_last_modified`"
        );
    }
    if (!sql_field_exists('cache_logs', 'log_last_modified')) {
        if (sql_field_exists('cache_logs', 'okapi_syncbase')) {
            $after = 'okapi_syncbase';
        } else {
            $after = 'last_modified';
        }
        sql(
            "ALTER TABLE `cache_logs` ADD COLUMN `log_last_modified` DATETIME NOT NULL COMMENT 'via Trigger (stat_caches, gk_item_waypoint)' AFTER `" . $after . '`'
        );
        sql(
            "UPDATE `cache_logs`
             SET `log_last_modified` = GREATEST(
                `last_modified`,
                IFNULL((SELECT MAX(`last_modified`) FROM `pictures` WHERE `pictures`.`object_type`=1 AND `pictures`.`object_id` = `cache_logs`.`id`),'0')
             )"
        );
    }
    if (!sql_field_exists('cache_logs_archived', 'log_last_modified')) {
        if (sql_field_exists('cache_logs_archived', 'okapi_syncbase')) {
            $after = 'okapi_syncbase';
        } else {
            $after = 'last_modified';
        }
        sql(
            'ALTER TABLE `cache_logs_archived` ADD COLUMN `log_last_modified` DATETIME NOT NULL AFTER `' . $after . '`'
        );
        sql('UPDATE `cache_logs_archived` SET `log_last_modified` = `last_modified`');
    }
}

function dbv_102(): void  // remove invisible caches from users' hidden stats
{
    sql(
        'INSERT IGNORE INTO `stat_user` (`user_id`)
            SELECT `user_id` FROM `caches` GROUP BY `user_id`'
    );
    sql(
        'UPDATE `stat_user`,
            (SELECT `user_id`, COUNT(*) AS `count`
             FROM `caches`
             INNER JOIN `cache_status`
                ON `cache_status`.`id`=`caches`.`status`
                AND `allow_user_view`=1
             GROUP BY `user_id`) AS `tblHidden`
         SET `stat_user`.`hidden`=`tblHidden`.`count`
         WHERE `stat_user`.`user_id`=`tblHidden`.`user_id`'
    );
    sql('CALL sp_refreshall_statpic()');
}

function dbv_103(): void  // update comments on static tables
{
    if (sql_table_exists('geodb_areas')) {
        sql("ALTER TABLE `geodb_areas`       COMMENT = 'not in use'");
    }
    if (sql_table_exists('geodb_changelog')) {
        sql("ALTER TABLE `geodb_changelog`   COMMENT = 'not in use'");
    }
    if (sql_table_exists('geodb_coordinates')) {
        sql("ALTER TABLE `geodb_coordinates` COMMENT = 'static content'");
    }
    if (sql_table_exists('geodb_floatdata')) {
        sql("ALTER TABLE `geodb_floatdata`   COMMENT = 'not in use'");
    }
    if (sql_table_exists('geodb_hierarchies')) {
        sql("ALTER TABLE `geodb_hierarchies` COMMENT = 'static content'");
    }
    if (sql_table_exists('geodb_intdata')) {
        sql("ALTER TABLE `geodb_intdata`     COMMENT = 'not in use'");
    }
    if (sql_table_exists('geodb_locations')) {
        sql("ALTER TABLE `geodb_locations`   COMMENT = 'static content'");
    }
    if (sql_table_exists('geodb_polygons')) {
        sql("ALTER TABLE `geodb_polygons`    COMMENT = 'not in use'");
    }
    if (sql_table_exists('geodb_search')) {
        sql("ALTER TABLE `geodb_search`      COMMENT = 'static content, not in use'");
    }
    if (sql_table_exists('geodb_textdata')) {
        sql("ALTER TABLE `geodb_textdata`    COMMENT = 'static content'");
    }
    if (sql_table_exists('geodb_type_names')) {
        sql("ALTER TABLE `geodb_type_names`  COMMENT = 'not in use'");
    }
    if (sql_table_exists('pw_dict')) {
        sql("ALTER TABLE `pw_dict`           COMMENT = 'static content'");
    }
    sql("ALTER TABLE `npa_areas`  COMMENT = 'static content'");
    sql("ALTER TABLE `npa_types`  COMMENT = 'static content'");
    sql("ALTER TABLE `nuts_codes` COMMENT = 'static content'");
    sql("ALTER TABLE `nuts_layer` COMMENT = 'static content'");
}

function dbv_104(): void  // added maintenance logs and OC team comments
{
    sql("ALTER TABLE `log_types_text` COMMENT = 'obsolete'");
    sql("ALTER TABLE `cache_logtype` COMMENT = 'obsolete'");
    sql("ALTER TABLE `log_types` CHANGE COLUMN `cache_status` `cache_status` TINYINT(1) NOT NULL DEFAULT '0'");
    sql('ALTER TABLE `log_types` CHANGE COLUMN `en` `en` VARCHAR(60) NOT NULL');
    if (!sql_field_exists('stat_caches', 'maintenance')) {
        sql('ALTER TABLE `stat_caches` ADD COLUMN `maintenance` SMALLINT(5) UNSIGNED NOT NULL AFTER `will_attend`');
    }
    if (!sql_field_exists('stat_cache_logs', 'maintenance')) {
        sql('ALTER TABLE `stat_cache_logs` ADD COLUMN `maintenance` SMALLINT(5) UNSIGNED NOT NULL AFTER `will_attend`');
    }
    if (!sql_field_exists('stat_user', 'maintenance')) {
        sql('ALTER TABLE `stat_user` ADD COLUMN `maintenance` SMALLINT(5) UNSIGNED NOT NULL AFTER `will_attend`');
    }
    if (!sql_field_exists('cache_logs', 'oc_team_comment')) {
        sql("ALTER TABLE `cache_logs` ADD COLUMN `oc_team_comment` TINYINT(1) NOT NULL DEFAULT '0' AFTER `type`");
    }
    if (!sql_field_exists('cache_logs_archived', 'oc_team_comment')) {
        sql(
            "ALTER TABLE `cache_logs_archived`
             ADD COLUMN `oc_team_comment` TINYINT(1) NOT NULL DEFAULT '0'
             AFTER `type`"
        );
    }
    // The new fields need not to be initialized, as these are new features and all
    // values are initially zero.
}

function dbv_105(): void  // HTML user profile texts
{
    if (!sql_field_exists('user', 'desc_htmledit')) {
        sql(
            "ALTER TABLE `user`
             ADD COLUMN `desc_htmledit` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'
             AFTER `data_license`"
        );
    }
    if (!sql_field_exists('user', 'description')) {
        sql('ALTER TABLE `user` ADD COLUMN `description` MEDIUMTEXT NOT NULL AFTER `data_license`');
        $rs = sql(
            'SELECT `user`.`user_id`,`user_options`.`option_value`
             FROM `user`,`user_options`
             WHERE `user_options`.`user_id`=`user`.`user_id` AND `user_options`.`option_id`=3'
        );
        while ($r = sql_fetch_array($rs)) {
            $text = nl2br(htmlspecialchars($r['option_value'], ENT_COMPAT, 'UTF-8'));
            sql("UPDATE `user` SET `description`='&2' WHERE `user_id`='&1'", $r['user_id'], $text);
        }
        sql_free_result($rs);
        // we keep the old entries in user_options for the case something went wrong here.
    }
}

function dbv_106(): void  // Cache status logging
{
    if (!sql_table_exists('cache_status_modified')) {
        sql(
            "CREATE TABLE `cache_status_modified` (
                `cache_id` INT(10) UNSIGNED NOT NULL,
                `date_modified` DATETIME NOT NULL,
                `old_state` TINYINT(2) UNSIGNED NOT NULL,
                `new_state` TINYINT(2) UNSIGNED NOT NULL,
                `user_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
                UNIQUE KEY `cache_id` (`cache_id`,`date_modified`)
             ) ENGINE=MyISAM"
        );
    }
}

function dbv_107(): void  // sync of table definitions, developer and production system
{
    sql(
        "ALTER TABLE `caches`
         MODIFY `meta_last_modified` DATETIME NOT NULL COMMENT 'via Trigger (stat_caches, gk_item_waypoint)'"
    );
    sql('ALTER TABLE `countries` MODIFY `en` VARCHAR(128) NOT NULL');
    if (!sql_index_exists('cache_reports', 'userid')) {
        sql('ALTER TABLE `cache_reports` ADD INDEX `userid` (`userid`)');
    }
}

/***** OC release 3.0.9 *****/

function dbv_108(): void  // automatic email-bounce processing
{
    if (!sql_field_exists('user', 'last_email_problem')) {
        sql('ALTER TABLE `user` ADD COLUMN `last_email_problem` DATETIME DEFAULT NULL AFTER `email_problems`');
    }
    if (!sql_field_exists('user', 'mailing_problems')) {
        sql(
            "ALTER TABLE `user`
             ADD COLUMN `mailing_problems` INT(10) UNSIGNED NOT NULL DEFAULT '0'
             AFTER `last_email_problem`"
        );
    }
}

function dbv_109(): void  // improved email-bounce processing
{
    if (!sql_field_exists('user', 'first_email_problem')) {
        sql('ALTER TABLE `user` ADD COLUMN `first_email_problem` DATE DEFAULT NULL AFTER `email_problems`');
    }
}

function dbv_110(): void  // move adoption history to separate table
{
    if (!sql_table_exists('cache_adoptions')) {
        sql(
            'CREATE TABLE `cache_adoptions` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `cache_id` INT(10) UNSIGNED NOT NULL,
                `date` DATETIME NOT NULL,
                `from_user_id` INT(10) UNSIGNED NOT NULL,
                `to_user_id` INT(10) UNSIGNED NOT NULL,
                PRIMARY KEY (`id`),
                KEY `cache_id` (`cache_id`,`date`)
             ) ENGINE=MyISAM AUTO_INCREMENT=1'
        );

        // Up to commit d15ee5f9, new cache notification logs were erroneously stored with
        // event ID 5 (instead of 8). Therefore we need to check for the module, too:
        $rs = sql(
            "SELECT `id`, `date_created`, `objectid1`, `logtext`
             FROM `logentries`
             WHERE `eventid`=5 AND `module`='cache'
             ORDER BY `date_created`, `id`"
        );
        while ($rLog = sql_fetch_assoc($rs)) {
            preg_match(
                '/Cache (\d+) has changed the owner from userid (\d+) to (\d+) by (\d+)/',
                $rLog['logtext'],
                $matches
            );
            if (count($matches) !== 5) {
                sql_free_result($rs);
                sql('DROP TABLE `cache_adoptions`');
                die("\nunknown adoption log entry format for ID " . $rLog['id'] . "\n");
            }
            sql(
                "INSERT INTO `cache_adoptions`
                    (`cache_id`,`date`,`from_user_id`,`to_user_id`)
                    VALUES ('&1','&2','&3','&4')",
                $rLog['objectid1'],
                $rLog['date_created'],
                $matches[2],
                $matches[3]
            );
        }
        sql_free_result($rs);

        // We keep the old entries in 'logentries' for the case something went wrong here.
    }
}

function dbv_111(): void  // fix event ID of old publishing notifications
{
    sql(
        "UPDATE `logentries` SET `eventid`=8
         WHERE `eventid`=5 AND `module`='notify_newcache'"
    );
}

function dbv_112(): void  // added maintained GC waypoints
{
    if (!sql_field_exists('caches', 'wp_gc_maintained')) {
        sql('ALTER TABLE `caches` ADD COLUMN `wp_gc_maintained` VARCHAR(7) NOT NULL AFTER `wp_gc`');
        sql("UPDATE `caches` SET `wp_gc_maintained`=UCASE(TRIM(`wp_gc`)) WHERE SUBSTR(TRIM(`wp_gc`),1,2)='GC'");
    }
    if (!sql_index_exists('caches', 'wp_gc_maintained')) {
        sql('ALTER TABLE `caches` ADD INDEX `wp_gc_maintained` (`wp_gc_maintained`)');
    }
}

function dbv_113(): void  // preventive, initial trigger update
{
    // The if-condition ensures that we will not downgrade to an old trigger
    // version for the case this function is re-run by some accident.
    // For future trigger updates, this will be ensured by the version
    // number returned by dbsvTriggerVersion().

    if (!sql_function_exists('dbsvTriggerVersion')) {
        update_triggers();
    }
}

function dbv_114(): void  // add dbsvTriggerVersion
{
    // dbsvTriggerVersion was introduced AFTER defining mutation #113 (it was
    // inserted there later). So we need to additionally install it on installations
    // which already updated to v113:

    update_triggers();
}

function dbv_115(): void  // remove obsolete functions
{
    update_triggers();
}

function dbv_116(): void    // optimize index for sorting logs
{
    sql(
        'ALTER TABLE `cache_logs`
         DROP INDEX `date`,
         ADD INDEX `date` (`cache_id`,`date`,`date_created`)'
    );
}

function dbv_117(): void    // add user profile flag for OConly notifications
{
    if (!sql_field_exists('user', 'notify_oconly')) {
        sql("ALTER TABLE `user` ADD COLUMN `notify_oconly` TINYINT(1) NOT NULL DEFAULT '1' AFTER `notify_radius`");
        sql('UPDATE `user` SET `notify_oconly`=0');
        // is default-enabled for new users but default-disabled for old users
    }
}

/***** OC release 3.0.10 *****/

function dbv_118(): void    // resize field password to fit to the new hashed passwords
{
    sql('ALTER TABLE `user` MODIFY COLUMN `password` VARCHAR(128) DEFAULT NULL');
}

function dbv_119(): void    // resize admin status field to enable more detailed rights
{
    sql("ALTER TABLE `user` MODIFY COLUMN `admin` SMALLINT(5) UNSIGNED NOT NULL DEFAULT '0'");
}

function dbv_120(): void    // remove obsolete tables of very old, discarded map code
{
    sql('DROP TABLE IF EXISTS `mapresult`');
    sql('DROP TABLE IF EXISTS `mapresult_data`');
}

/***** OC release 3.0.11 *****/

function dbv_121(): void    // add user profile flag for receiving newsletter
{
    if (!sql_field_exists('user', 'accept_mailing')) {
        sql("ALTER TABLE `user` ADD COLUMN `accept_mailing` TINYINT(1) NOT NULL DEFAULT '1' AFTER `mailing_problems`");
    }
}

/***** OC release 3.0.12 *****/

function dbv_122(): void    // add user profile flag for default setting of send-my-email option
{
    if (!sql_field_exists('user', 'usermail_send_addr')) {
        sql(
            "ALTER TABLE `user` ADD COLUMN `usermail_send_addr` TINYINT(1) NOT NULL DEFAULT '0' AFTER `accept_mailing`"
        );
    }
}

/***** OC release 3.0.13 *****/

function dbv_123(): void  // add tables, fields and procs for cache lists and list watches
{
    if (!sql_table_exists('cache_lists')) {
        sql(
            "CREATE TABLE `cache_lists` (
                `id` INT(10) NOT NULL AUTO_INCREMENT,
                `uuid` VARCHAR(36) NOT NULL,
                `user_id` INT(10) NOT NULL,
                `date_created` DATETIME NOT NULL,
                `last_modified` DATETIME NOT NULL,
                `last_added` DATETIME DEFAULT NULL,
                `name` VARCHAR(80) NOT NULL,
                `is_public` TINYINT(1) NOT NULL DEFAULT '0',
                `entries` INT(6) NOT NULL DEFAULT '0' COMMENT 'via trigger in cache_list_items',
                `watchers` INT(10) NOT NULL DEFAULT '0' COMMENT 'via trigger in cache_list_watches',
                PRIMARY KEY  (`id`),
                UNIQUE KEY `uuid` (`uuid`),
                KEY `name` (`name`),
                KEY `user_id` (`user_id`)
             ) ENGINE=MyISAM"
        );
    }
    if (!sql_table_exists('cache_list_items')) {
        sql(
            'CREATE TABLE `cache_list_items` (
                `cache_list_id` INT(10) NOT NULL,
                `cache_id` INT(10) NOT NULL,
                UNIQUE KEY `cache_list_id` (`cache_list_id`,`cache_id`),
                KEY `cache_id` (`cache_id`)
             ) ENGINE=MyISAM'
        );
    }
    if (!sql_table_exists('cache_list_watches')) {
        sql(
            'CREATE TABLE `cache_list_watches` (
                `cache_list_id` INT(10) NOT NULL,
                `user_id` INT(10) NOT NULL,
                UNIQUE KEY `cache_list_id` (`cache_list_id`,`user_id`),
                KEY `user_id` (`user_id`)
             ) ENGINE=MyISAM'
        );
    }

    if (!sql_field_exists('caches', 'show_cachelists')) {
        sql("ALTER TABLE `caches` ADD COLUMN `show_cachelists` TINYINT(1) NOT NULL DEFAULT '1'");
    }
    if (sql_field_exists('cache_watches', 'last_executed')) {  // obsolete pre-OC3 field
        sql('ALTER TABLE `cache_watches` DROP COLUMN `last_executed`');
    }
}

function dbv_124(): void  // update cache lists implementation
{
    if (!sql_table_exists('stat_cache_lists')) {
        sql(
            "CREATE TABLE `stat_cache_lists` (
                `cache_list_id` INT(10) NOT NULL,
                `entries` INT(6) NOT NULL DEFAULT '0' COMMENT 'via trigger in cache_list_items',
                `watchers` INT(6) NOT NULL DEFAULT '0' COMMENT 'via trigger in cache_list_watches',
                PRIMARY KEY (`cache_list_id`)
             ) ENGINE=MyISAM
                SELECT `id` `cache_list_id`, `entries`, `watchers` FROM `cache_lists`"
        );
    }
    if (sql_field_exists('cache_lists', 'entries')) {
        sql('ALTER TABLE `cache_lists` DROP COLUMN `entries`');
    }
    if (sql_field_exists('cache_lists', 'watchers')) {
        sql('ALTER TABLE `cache_lists` DROP COLUMN `watchers`');
    }
    if (!sql_field_exists('cache_lists', 'description')) {
        sql('ALTER TABLE `cache_lists` ADD COLUMN `description` MEDIUMTEXT NOT NULL');
    }
    if (!sql_field_exists('cache_lists', 'desc_htmledit')) {
        sql("ALTER TABLE `cache_lists` ADD COLUMN `desc_htmledit` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'");
    }
}

function dbv_125(): void  // update cache lists implementation; preparation of XML interface export
{
    global $opt;

    if (!sql_field_exists('cache_lists', 'node')) {
        sql("ALTER TABLE `cache_lists` ADD COLUMN `node` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' AFTER `uuid`");
        sql("UPDATE `cache_lists` SET `node`='&1'", $opt['logic']['node']['id']);
    }
    if (!sql_field_exists('cache_lists', 'last_state_change')) {
        sql('ALTER TABLE `cache_lists` ADD COLUMN `last_state_change` DATETIME DEFAULT NULL AFTER `last_added`');
    }
}

function dbv_126(): void  // clean up data of disabled accounts
{
    sql('DELETE FROM `cache_adoption` WHERE `user_id` IN (SELECT `user_id` FROM `user` WHERE `is_active_flag`=0)');
    sql('DELETE FROM `cache_ignore`   WHERE `user_id` IN (SELECT `user_id` FROM `user` WHERE `is_active_flag`=0)');
    sql('DELETE FROM `cache_watches`  WHERE `user_id` IN (SELECT `user_id` FROM `user` WHERE `is_active_flag`=0)');
}

/***** Hotfixes *****/

function dbv_127(): void  // fix name of Dessau-Köthen
{
    sql("UPDATE `nuts_codes` SET `name`='Köthen' WHERE `code`='DEE15'");
    sql("UPDATE `cache_location` SET `adm4`='Köthen' WHERE `code4`='DEE15'");
}

function dbv_128(): void  // see util2/gns/mksearchindex.php; fix for #175/3
{
    sql('DELETE FROM `gns_search`');
    if (sql_field_exists('gns_search', 'id')) {
        sql('ALTER TABLE `gns_search` DROP COLUMN `id`');
    }
    // unused, does not make sense; will also drop primary index

    $rs = sql("SELECT `uni`, `full_name_nd` FROM `gns_locations` WHERE `dsg` LIKE 'PPL%'");
    while ($r = sql_fetch_array($rs)) {
        $text = search_text2sort($r['full_name_nd'], true);
        if (preg_match('/[a-z]+/', $text)) {
            $simpletext = search_text2simple($text);
            sql(
                "INSERT INTO `gns_search`
                    (`uni_id`, `sort`, `simple`, `simplehash`)
                    VALUES ('&1', '&2', '&3', '&4')",
                $r['uni'],
                $text,
                $simpletext,
                sprintf('%u', crc32($simpletext))
            );
        }
    }
    mysqli_free_result($rs);
}

/***** OC release 3.0.14 *****/

function dbv_129(): void  // cache list passwords & bookmarking
{
    if (!sql_field_exists('cache_lists', 'password')) {
        sql('ALTER TABLE `cache_lists` ADD COLUMN `password` VARCHAR(80) NOT NULL');
    }
    if (!sql_table_exists('cache_list_bookmarks')) {
        sql(
            'CREATE TABLE `cache_list_bookmarks` (
                `cache_list_id` INT(10) NOT NULL,
                `user_id` INT(10) NOT NULL,
                `password` VARCHAR(80) NOT NULL,
                UNIQUE KEY `cache_list_id` (`cache_list_id`,`user_id`),
                KEY `user_id` (`user_id`)
             ) ENGINE=MyISAM'
        );
    }
}

function dbv_130(): void  // discarded text editor mode (#236)
{
    sql(
        "ALTER TABLE `cache_desc`
         CHANGE COLUMN `desc_html` `desc_html` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'obsolete'"
    );
    sql("ALTER TABLE `cache_desc` CHANGE COLUMN `desc_htmledit` `desc_htmledit` TINYINT(1) NOT NULL DEFAULT '1'");
    sql(
        "ALTER TABLE `cache_logs`
         CHANGE COLUMN `text_html` `text_html` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'obsolete'"
    );
    sql("ALTER TABLE `cache_logs`
         CHANGE COLUMN `text_htmledit` `text_htmledit` TINYINT(1) NOT NULL DEFAULT '1'");
    sql(
        "ALTER TABLE `user`
         CHANGE COLUMN `no_htmledit_flag` `no_htmledit_flag` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'inverted meaning'"
    );
}

function dbv_131(): void  // add native language names (#109)
{
    if (!sql_field_exists('languages', 'native_name')) {
        sql('ALTER TABLE `languages` ADD COLUMN `native_name` VARCHAR(60) NOT NULL AFTER `trans_id`');
    }
}

function dbv_132(): void  // fix cache list node IDs
{
    global $opt;
    sql("UPDATE `cache_lists` SET `node`='&1' WHERE `node`=0", $opt['logic']['node']['id']);
}

function dbv_133(): void  // add user language for notification emails (#141)
{
    if (!sql_field_exists('user', 'language')) {
        sql('ALTER TABLE `user` ADD COLUMN `language` CHAR(2) DEFAULT NULL AFTER `notify_oconly`');
    }
}

function dbv_134(): void  // fix removed cache list node IDs
{
    global $opt;
    sql(
        "UPDATE `removed_objects` SET `node`='&1' WHERE `type`=8 AND `node`=0",
        $opt['logic']['node']['id']
    );
}

function dbv_135(): void  // move KML cache type names from search.kml.inc.php to database
{
    if (!sql_field_exists('cache_type', 'kml_name')) {
        sql('ALTER TABLE `cache_type` ADD COLUMN `kml_name` VARCHAR(10) NOT NULL');
    }
}

function dbv_136(): void  // move main town table from settings into database
{
    if (!sql_table_exists('towns')) {
        sql(
            "CREATE TABLE `towns` (
                `country` CHAR(2) NOT NULL,
                `name` VARCHAR(40) NOT NULL,
                `trans_id` INT(10) UNSIGNED NOT NULL,
                `coord_lat` DOUBLE NOT NULL,
                `coord_long` DOUBLE NOT NULL,
                `maplist` TINYINT(1) NOT NULL DEFAULT '0',
                KEY `country` (`country`)
             ) ENGINE=MyISAM"
        );
    }
}

function dbv_137(): void  // partial revert of mutation 130
{
    sql("ALTER TABLE `cache_desc` CHANGE COLUMN `desc_html` `desc_html` TINYINT(1) NOT NULL DEFAULT '1' COMMENT ''");
    sql("ALTER TABLE `cache_logs` CHANGE COLUMN `text_html` `text_html` TINYINT(1) NOT NULL DEFAULT '1' COMMENT ''");
    sql(
        "ALTER TABLE `user`
         CHANGE COLUMN `no_htmledit_flag` `no_htmledit_flag` TINYINT(1) NOT NULL DEFAULT '0' COMMENT ''"
    );
}

function dbv_138(): void  // add some reasonable indices, e.g. to optimize special-purpose deletion functions
{
    if (!sql_index_exists('cache_reports', 'cacheid')) {
        sql('ALTER TABLE `cache_reports` ADD INDEX `cacheid` (`cacheid`)');
    }

    if (!sql_index_exists('cache_adoptions', 'from_user_id')) {
        sql('ALTER TABLE `cache_adoptions` ADD INDEX `from_user_id` (`from_user_id`)');
    }

    if (!sql_index_exists('cache_adoptions', 'to_user_id')) {
        sql('ALTER TABLE `cache_adoptions` ADD INDEX `to_user_id` (`to_user_id`)');
    }

    if (!sql_index_exists('cache_watches', 'user_id')) {
        sql('ALTER TABLE `cache_watches` ADD INDEX `user_id` (`user_id`)');
    }

    if (!sql_index_exists('notify_waiting', 'user_id')) {
        sql('ALTER TABLE `notify_waiting` ADD INDEX `user_id` (`user_id`)');
    }

    if (!sql_index_exists('stat_cache_logs', 'user_id')) {
        sql('ALTER TABLE `stat_cache_logs` ADD INDEX `user_id` (`user_id`)');
    }

    if (!sql_index_exists('watches_logqueue', 'user_id')) {
        sql('ALTER TABLE `watches_logqueue` ADD INDEX `user_id` (`user_id`)');
    }
}

function dbv_139(): void
{
    if (!sql_field_exists('user', 'language_guessed')) {
        sql("ALTER TABLE `user` ADD COLUMN `language_guessed` TINYINT(1) NOT NULL DEFAULT '0' AFTER `language`");
    }
}

function dbv_140(): void   // last-used user domain, for email contents
{
    if (!sql_field_exists('user', 'domain')) {
        sql('ALTER TABLE `user` ADD COLUMN `domain` VARCHAR(40) DEFAULT NULL AFTER `language_guessed`');
    }
}

function dbv_141(): void   // adjust some comments
{
    sql("ALTER TABLE `cache_logs` MODIFY `log_last_modified` DATETIME NOT NULL COMMENT 'via Triggers'");
    sql("ALTER TABLE `log_types` MODIFY `icon_small` VARCHAR(255) NOT NULL COMMENT ''");
}

function dbv_142(): void   // drop obsolete table
{
    // This table has/had an index over a 255 chars column, which would produce
    // the error "index too long (maximum is 1000 chars)" when trying to convert
    // to utf8mb4 charset. We drop it here before an utf8 migration may be run.

    if (sql_table_exists('search_words')) {
        sql('DROP TABLE `search_words`');
    }
}

/***** OC release 3.0.15 *****/

/***** OC release 3.0.16 *****/

function dbv_143(): void   // navicache WP is obsolete
{
    sql("ALTER TABLE `caches` MODIFY `wp_nc` VARCHAR(6) NOT NULL COMMENT 'obsolete'");
}

function dbv_144(): void   // add log versioning to allow log vandalism restore
{
    if (!sql_table_exists('cache_logs_modified')) {
        sql(
            'CREATE TABLE `cache_logs_modified` (
                `id` INT(10) UNSIGNED NOT NULL,
                `uuid` VARCHAR(36) NOT NULL,
                `node` TINYINT(3) UNSIGNED NOT NULL,
                `date_created` DATETIME NOT NULL,
                `last_modified` DATETIME NOT NULL,
                `log_last_modified` DATETIME NOT NULL,
                `cache_id` INT(10) UNSIGNED NOT NULL,
                `user_id` INT(10) UNSIGNED NOT NULL,
                `type` TINYINT(3) UNSIGNED NOT NULL,
                `oc_team_comment` TINYINT(1) NOT NULL,
                `date` DATETIME NOT NULL,
                `text` MEDIUMTEXT NOT NULL,
                `text_html` TINYINT(1) NOT NULL,
                `modify_date` DATETIME DEFAULT NULL,
                KEY `id` (`id`, `modify_date`)
             ) ENGINE=MyISAM'
        );
    }
}

function dbv_145(): void   // optimize log change recording
{
    sql(
        'ALTER TABLE `cache_logs_modified`
         DROP INDEX `id`,
         ADD UNIQUE INDEX `id` (`id`, `modify_date`)'
    );
    sql('ALTER TABLE `cache_logs_modified` MODIFY `modify_date` DATE DEFAULT NULL');
    // This may produce an error for duplicate dates.
    // You may just delete the old `cache_logs_modified` contents
    // (the feature was started just a few days before this change).
}

/***** OC release 3.0.17 *****/

function dbv_146(): void   // NM flags
{
    if (!sql_field_exists('cache_logs', 'needs_maintenance')) {
        sql("ALTER TABLE `cache_logs`
             ADD COLUMN `needs_maintenance` TINYINT(1) NOT NULL DEFAULT '0'
             AFTER `date`");
    }
    if (!sql_field_exists('cache_logs', 'listing_outdated')) {
        sql(
            "ALTER TABLE `cache_logs`
             ADD COLUMN `listing_outdated` TINYINT(1) NOT NULL DEFAULT '0'
             AFTER `needs_maintenance`"
        );
    }

    if (!sql_field_exists('cache_logs_modified', 'needs_maintenance')) {
        sql(
            "ALTER TABLE `cache_logs_modified`
             ADD COLUMN `needs_maintenance` TINYINT(1) NOT NULL DEFAULT '0'
             AFTER `date`"
        );
    }
    if (!sql_field_exists('cache_logs_modified', 'listing_outdated')) {
        sql(
            "ALTER TABLE `cache_logs_modified`
             ADD COLUMN `listing_outdated` TINYINT(1) NOT NULL DEFAULT '0'
             AFTER `needs_maintenance`"
        );
    }

    if (!sql_field_exists('cache_logs_archived', 'needs_maintenance')) {
        sql(
            "ALTER TABLE `cache_logs_archived`
             ADD COLUMN `needs_maintenance` TINYINT(1) NOT NULL DEFAULT '0'
             AFTER `date`"
        );
    }
    if (!sql_field_exists('cache_logs_archived', 'listing_outdated')) {
        sql(
            "ALTER TABLE `cache_logs_archived`
             ADD COLUMN `listing_outdated` TINYINT(1) NOT NULL DEFAULT '0'
             AFTER `needs_maintenance`"
        );
    }

    if (!sql_field_exists('caches', 'needs_maintenance')) {
        sql("ALTER TABLE `caches` ADD COLUMN `needs_maintenance` TINYINT(1) NOT NULL DEFAULT '0'");
    }
    if (!sql_field_exists('caches', 'listing_outdated')) {
        sql("ALTER TABLE `caches` ADD COLUMN `listing_outdated` TINYINT(1) NOT NULL DEFAULT '0'");
    }
    if (!sql_field_exists('caches', 'flags_last_modified')) {
        sql("ALTER TABLE `caches` ADD COLUMN `flags_last_modified` DATETIME NOT NULL COMMENT 'via Trigger (caches)'");
    }
}

function dbv_147(): void
{
    if (!sql_field_exists('log_types', 'maintenance_logs')) {
        sql('ALTER TABLE `log_types` ADD COLUMN `maintenance_logs` TINYINT(1) NOT NULL');
    }
}

function dbv_148(): void   // add log contents modification date
{
    if (!sql_field_exists('cache_logs', 'entry_last_modified')) {
        sql(
            "ALTER TABLE `cache_logs`
             ADD COLUMN `entry_last_modified` DATETIME NOT NULL COMMENT 'via Trigger (cache_logs)'
             AFTER `date_created`"
        );
        sql('UPDATE `cache_logs` SET `entry_last_modified`=`date_created`');
    }
    if (!sql_field_exists('cache_logs_archived', 'entry_last_modified')) {
        sql(
            'ALTER TABLE `cache_logs_archived` ADD COLUMN `entry_last_modified` DATETIME NOT NULL AFTER `date_created`'
        );
        sql('UPDATE `cache_logs_archived` SET `entry_last_modified`=`date_created`');
    }
    if (!sql_field_exists('cache_logs_modified', 'entry_last_modified')) {
        sql(
            'ALTER TABLE `cache_logs_modified`
             ADD COLUMN `entry_last_modified` DATETIME NOT NULL AFTER `date_created`'
        );
        sql('UPDATE `cache_logs_modified` SET `entry_last_modified`=`date_created`');
    }
}

function dbv_149(): void   // add editcache flag to protect old coordinates
{
    if (!sql_field_exists('caches', 'protect_old_coords')) {
        sql(
            "ALTER TABLE `caches`
             ADD COLUMN `protect_old_coords` TINYINT(1) NOT NULL DEFAULT '0'
             AFTER `show_cachelists`"
        );
    }
}

/***** OC release 3.0.18 *****/

function dbv_150(): void   // add history of reported waypoints
{
    if (!sql_table_exists('waypoint_reports')) {
        sql(
            "CREATE TABLE `waypoint_reports` (
                `report_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `date_reported` DATETIME NOT NULL,
                `wp_oc` VARCHAR(7) NOT NULL,
                `wp_external` VARCHAR(8) NOT NULL,
                `source` VARCHAR(64) NOT NULL,
                `gcwp_processed` TINYINT(1) NOT NULL DEFAULT '0',
                PRIMARY KEY  (`report_id`),
                KEY `gcwp_processed` (`gcwp_processed`,`date_reported`)
             ) ENGINE=MyISAM"
        );
    }
}

function dbv_151(): void   // new date field for ordering logs
{
    if (!sql_field_exists('cache_logs', 'order_date')) {
        sql('ALTER TABLE `cache_logs` ADD COLUMN `order_date` DATETIME NOT NULL AFTER `date`');
        sql(
            "UPDATE `cache_logs`
             SET `order_date` =
                IF(RIGHT(`date`, 8) <> '00:00:00' OR `date` > `date_created`, `date`,
                   IF(LEFT(`date_created`, 10) = LEFT(`date`, 10), `date_created`,
                      CONCAT(LEFT(`date`, 11), '23:59:58')
                   )
                )"
        );
    }
    if (!sql_index_exists('cache_logs', 'order_date')) {
        sql('ALTER TABLE `cache_logs` ADD INDEX `order_date` (`cache_id`,`order_date`,`date_created`,`id`)');
    }
    if (!sql_field_exists('cache_logs_archived', 'order_date')) {
        sql('ALTER TABLE `cache_logs_archived` ADD COLUMN `order_date` DATETIME NOT NULL AFTER `date`');
        sql(
            "UPDATE `cache_logs_archived`
             SET `order_date` =
                IF(RIGHT(`date`, 8) <> '00:00:00' OR `date` > `date_created`, `date`,
                   IF(LEFT(`date_created`, 10) = LEFT(`date`, 10), `date_created`,
                      CONCAT(LEFT(`date`, 11), '23:59:58')
                   )
                )"
        );
    }
}

function dbv_152(): void
{
    if (!sql_field_exists('cache_reports', 'comment')) {
        sql('ALTER TABLE `cache_reports` ADD COLUMN `comment` MEDIUMTEXT NOT NULL');
    }
}

function dbv_153(): void  // generic trigger update, see notes on "maintain.php update"
{
    update_triggers();
}

function dbv_154(): void  // add pictures order option
{
    if (!sql_field_exists('pictures', 'seq')) {
        sql("ALTER TABLE `pictures` ADD COLUMN `seq` SMALLINT(5) NOT NULL DEFAULT '0'");
    }

    // initialize the new ordering field
    if (sql_value('SELECT COUNT(*) FROM `pictures` WHERE `seq` = 0', 0)) {
        $rs = sql(
            'SELECT `id`, `object_type`, `object_id`
             FROM `pictures`
             ORDER BY `object_type`, `object_id`, `date_created`'
        );
        $prev_object_type = -1;
        $prev_object_id = -1;

        // Prevent updating pictures.last_modified for the case that
        // the new triggers are already installed:
        sql('SET @XMLSYNC=1');
        while ($r = sql_fetch_assoc($rs)) {
            if ($r['object_type'] != $prev_object_type ||
                $r['object_id'] != $prev_object_id
            ) {
                $seq = 1;
            }
            sql("UPDATE `pictures` SET `seq`='&2' WHERE `id`='&1'", $r['id'], $seq);
            ++$seq;
            $prev_object_type = $r['object_type'];
            $prev_object_id = $r['object_id'];
        }
        sql('SET @XMLSYNC=0');
        sql_free_result($rs);
    }

    sql(
        'ALTER TABLE `pictures`
         DROP INDEX `object_type`,
         ADD UNIQUE INDEX `object_type` (`object_type`,`object_id`,`seq`)'
    );
}

function dbv_155(): void
{
    if (!sql_field_exists('cache_report_reasons', 'order')) {
        sql('ALTER TABLE `cache_report_reasons` ADD COLUMN `order` TINYINT(2) UNSIGNED NOT NULL');
    }
}

function dbv_156(): void  // clean up data created by bad cacheLogsBeforeUpdate trigger
{
    sql("DELETE FROM `cache_logs_modified` WHERE `date` = '0000-00-00 00:00:00'");
}

/***** OC release 3.0.19 *****/

function dbv_157(): void   // discard news entry system
{
    // The feature of displaying news via `news` table stays for now,
    // but the feature of entering news via the OC website is discarded.

    sql(
        "UPDATE `user`
         SET `admin` = `admin` \& ~'&1'
         WHERE (`admin` \& 255) <> 255",
        ADMIN_NEWS
    );
}

function dbv_158(): void
{
    sql(
        "ALTER TABLE `cache_logs`
            COMMENT = 'Attention: modifications to this table may need to be " .
                      'applied also to cache_logs_archived, cache_logs_modified ' .
                      "and trigger cacheLogsBeforeUpdate!'"
    );
}

/***** OC release 3.1.4 *****/

function dbv_159(): void
{
    // optimization for OKAPI-search 'date_hidden' sorting option
    if (!sql_index_exists('caches', 'date_hidden')) {
        sql('ALTER TABLE `caches` ADD INDEX `date_hidden` (`date_hidden`)');
    }
}

function dbv_160(): void
{
    // dummy function for obsolete mutation, replaced by 165
}

function dbv_161(): void
{
    global $opt;

    // add nuts codes for Bulgaria, Kroatia, Romania and Turkey
    system(
        'cat ' . __DIR__ . '/../sql/static-data/nuts_codes.sql |' .
        ' mysql -h' . $opt['db']['servername'] . ' -u' . $opt['db']['username'] .
        ' --password=' . $opt['db']['password'] . ' ' . $opt['db']['placeholder']['db']
    );

    // initiate recalculation of cache locations for these countries

    // temporarily get rid of the update trigger so that we can change last_modified;
    // trigger will be fixed and reinstalled by mutation 163
    update_triggers();
    sql(
        "UPDATE `cache_location`
         SET `last_modified` = '2000-01-01 00:00:00'
         WHERE `code1` IN ('BG', 'HR', 'RO', 'TR')"
    );

    // Cache locations will be updated by the next cronjob run.
}

function dbv_162(): void
{
    // dummy function for obsolete mutation, replaced by 163
}

function dbv_163(): void
{
    // fix cache_location triggers
    update_triggers();
}

/***** OC release 3.1.5 *****/

function dbv_164(): void
{
    global $opt;

    // correct spelling of lots of NUTS 1 entities from lower- to uppercase;
    // add missing NUTS 1 and 3 codes for Norway;
    // change UK NUTS codes from UK to GB, which is the "OC country code" for UK
    system(
        'cat ' . __DIR__ . '/../sql/static-data/nuts_codes.sql |' .
        ' mysql -h' . $opt['db']['servername'] . ' -u' . $opt['db']['username'] .
        ' --password=' . $opt['db']['password'] . ' ' . $opt['db']['placeholder']['db']
    );
    sql(
        "UPDATE `nuts_layer`
         SET `code` = CONCAT('GB', SUBSTR(`code`, 3))
         WHERE `code` LIKE 'UK%'"
    );

    // update NUTS 1 cache location names
    sql(
        "UPDATE
            `cache_location` `cl`,
            (SELECT `code`, `name` FROM `nuts_codes` WHERE `code` LIKE '___') AS `nc`
         SET `adm2` = `nc`.`name`
         WHERE `nc`.`code` = `cl`.`code2`
         AND `cl`.`code1` != 'DE'"   // optimization - there are no changes for Germany
    );

    // Update outdated Sweden NUTS 2 and 3 codes in nuts_layer table.
    // NUTS 1 would need completely new geographic data; but the
    // cache_location cronjob won't need them - L1 and L2 data is derived from L3.
    $sweden_nuts = [
        'SE01'  => 'SE11',
        'SE010' => 'SE110',
        'SE02'  => 'SE12',
        'SE021' => 'SE121',
        'SE022' => 'SE122',
        'SE023' => 'SE123',
        'SE024' => 'SE124',
        'SE025' => 'SE125',
        'SE09'  => 'SE21',
        'SE091' => 'SE211',
        'SE092' => 'SE212',
        'SE093' => 'SE213',
        'SE094' => 'SE214',
        'SE04'  => 'SE22',
        'SE041' => 'SE221',
        'SE044' => 'SE224',
        'SE0A'  => 'SE23',
        'SE0A1' => 'SE231',
        'SE0A2' => 'SE232',
        'SE06'  => 'SE31',
        'SE061' => 'SE311',
        'SE062' => 'SE312',
        'SE063' => 'SE313',
        'SE07'  => 'SE32',
        'SE071' => 'SE321',
        'SE072' => 'SE322',
        'SE08'  => 'SE33',
        'SE081' => 'SE331',
        'SE082' => 'SE332',
    ];
    foreach ($sweden_nuts as $old => $new) {
        sql("UPDATE `nuts_layer` SET `code`='&2' WHERE `code`='&1'", $old, $new);
    }

    // recalculate all UK, Norway, Sweden locations
    sql(
        "DELETE FROM `cache_location`
         WHERE `code1` IN ('UK', 'NO', 'SE')"
    );
    // Next cache_location cronjob run will fill in the CL data.
}

function dbv_165(): void
{
    // remove trailing "\r" and double-spaces from ~ 7600 NPA names
    sql("UPDATE `npa_areas` SET `name` = REPLACE(REPLACE(`name`, '  ', ' '), '\r', '')");

    // fix broken non-ASCII characters in NPA names
    $npa_names = [
        'Bürgerheide',
        'Bärwalder Ländchen',
        'Biosphärenreservat Schorfheide - Chorin',
        'Biosphärenreservat Spreewald',
        'Calau/Altdöbern/Reddern',
        'Diedersdorfer Heide und Großbeerener Graben',
        'Diehloer Höhen',
        'Ehemaliges Grubengelände Finkenheerd',
        'Elbaue Mühlberg',
        'Elsteraue zwischen Herzberg und Übigau',
        'Fürstenberger Wald- und Seengebiet',
        'Fauler See, Märkischer Naturgarten, Güldendorfer Mühlental, Eichwald und Buschmühle',
        'Göhlensee',
        'Görnsee und Görnberg',
        'Groß-Leuthener See und Dollgen See',
        'Groß-See',
        'Grubenseen in der Rückersdorfer Heide',
        'Gubener Fließtäler',
        'Hügelgebiet um den Langen Berg',
        'Hohenleipisch-Sornoer-Altmoränenlandschaft',
        'Hoher Fläming - Belziger Landschaftswiesen',
        'Königswald mit Havelseen und Seeburger Agrarlandschaft',
        'Körbaer Teich und Lebusaer Waldgebiet',
        'Köthener See',
        'Kiesgruben Eisenhüttenstadt',
        'Lampfert bei Kröbeln',
        'LSG-Auf\'m Hoevel',
        'LSG-Brünen Ost',
        'LSG-Grünlandniederung Gesthuysen und Vynsche Ley',
        'LSG-Hauptterrasse südlich Hünxe',
        'LSG-Offenland zwischen der Hees und Fürstenberg',
        'LSG-Ostmünsterland',
        'LSG-Südlich Gahlen',
        'LSG-Südlicher Tüschenwald',
        'LSG-Stammshütte',
        'Müggelspree-Löcknitzer Wald- und Seengebiet',
        'Märkische Schweiz',
        'Merzdorf / Hirschfelder Waldhöhen',
        'Naturpark Märkische Schweiz',
        'Nauen-Brieselang-Krämer',
        'Niederungssystem des Fredersdorfer Mühlenfließes und seiner Vorfluter',
        'Niederungssystem des Neuenhagener Mühlenfließes und seiner Vorfluter',
        'Niederungssystem des Zinndorfer Mühlenfließes und seiner Vorfluter',
        'Neißeaue im Kreis Forst',
        'Neißeaue um Grießen',
        'Norduckermärkische Seenlandschaft',
        'NSG Diergardt\'scher Wald',
        'NSG Plaesterlegge - Auf\'m Kipp',
        'NSG Vor\'m Haengeberg',
        'Oderhänge Seelow-Lebus',
        'Odervorland Groß Neuendorf-Lebus',
        'Ölsiger Luch',
        'Pfählingsee - Prierowsee',
        'Rückersdorf-Drößiger-Heidelandschaft',
        'Reptener Mühlenfließ',
        'Südostniederbarnimer Weiherketten',
        'Scharmützelseegebiet',
        'Schlagsdorfer Waldhöhen',
        'Seenkette des Platkower Mühlenfließes / Heidelandschaft Worin',
        'Spreeaue südlich Cottbus',
        'Staubeckenlandschaft Bräsinchen - Spremberg',
        'Steinitz-Geisendorfer Endmoränenlandschaft',
        'Taeler Brühl- und Schleidgraben',
        'Teupitz - Köriser Seengebiet',
        'Trepliner Seen, Booßener und Altzeschdorfer Mühlenfließ',
        'Wald- und Restseengebiet Döbern',
        'Wiesen- und Ackerlandschaft Ströbitz/Kolkwitz',
        'Wiesen- und Teichlandschaft Kolkwitz/Hänchen',
    ];
    foreach ($npa_names as $npa_name) {
        $wrong_name = str_replace(['Ä', 'ä', 'Ö', 'ö', 'Ü', 'ü', "'"], '?', $npa_name);
        $wrong_name = str_replace('ß', 'á', $wrong_name);
        sql("UPDATE `npa_areas` SET `name`='&2' WHERE `name`=BINARY '&1'", $wrong_name, $npa_name);
    }
    $npa_names = [
        'LSG-Am Vinnenberger Busch - Gro?er Dyk' => 'LSG-Dingdener und Brüner Höhen',
        'LSG-Boxteler Bahn zwischen Gemeindegrenze Uedem und Xanten - Trajanstra?e' => 'LSG-Boxteler Bahn zwischen Gemeindegrenze Uedem und Xanten - Trajanstraße',
        'LSG-Dingdener und Br?ner H÷hen' => 'LSG-Am Vinnenberger Busch - Großer Dyk',
        'LSG-Hufscher Berg - L÷wenberg' => 'LSG-Hufscher Berg - Löwenberg',
        'LSG-Landwehren s?dlich der Weseler Stra?e' => 'LSG-Landwehren südlich der Weseler Straße',
        'LSG-Niederung K÷rversley/ Marienbaumer Graben' => 'LSG-Niederung Körversley/ Marienbaumer Graben',
        'LSG-Niederungen s?dlich und ÷stlich Grenzdyck' => 'LSG-Niederungen südlich und östlich Grenzdyck',
        'LSG-Tannenspeet - Gro?enbusch' => 'LSG-Tannenspeet - Großenbusch',
    ];
    foreach ($npa_names as $wrong_name => $npa_name) {
        sql("UPDATE `npa_areas` SET `name`='&2' WHERE `name`=BINARY '&1'", $wrong_name, $npa_name);
    }
}

function dbv_166(): void
{
    // initiate complete rebuild of fulltext search index;
    // see https://redmine.opencaching.de/issues/1043

    sql('TRUNCATE TABLE `search_index`');
    sql('TRUNCATE TABLE `search_index_times`');

    // Adjust the search_index_times definition to the changed implementation.
    // Field types are all ok.
    sql("ALTER TABLE `search_index_times` COMMENT = 'search_index entries that need an update'");
    sql(
        "ALTER TABLE `search_index_times`
         CHANGE COLUMN `object_id` `object_id` int(10) unsigned NOT NULL COMMENT 'cache ID'"
    );

    sql(
        'INSERT INTO `search_index_times`
         (`object_type`, `object_id`, `last_refresh`)
         SELECT 2, `cache_id`, NOW() FROM `caches` UNION   -- cache names
         SELECT 1, `cache_id`, NOW() FROM `caches` UNION   -- log entries
         SELECT 3, `cache_id`, NOW() FROM `caches` UNION   -- cache descriptions
         SELECT 6, `cache_id`, NOW() FROM `caches`         -- picture titles'
    );
}

function dbv_167(): void
{
    sql("DELETE FROM `sys_cron` WHERE `name` = 'okapi_cleanup'");
}

function dbv_168(): void
{
    // add nuts entity display selection to countries
    if (!sql_field_exists('countries', 'adm_display2')) {
        sql('ALTER TABLE `countries` ADD COLUMN `adm_display2` TINYINT(1) NOT NULL');
    }
    if (!sql_field_exists('countries', 'adm_display3')) {
        sql('ALTER TABLE `countries` ADD COLUMN `adm_display3` TINYINT(1) NOT NULL');
    }

    // improve some NUTS 2 entity names
    sql("UPDATE `cache_location` SET `adm3`='Burgenland' WHERE `code3`='AT11'");
    sql("UPDATE `cache_location` SET `adm3`='Prov. Limburg' WHERE `code3`='BE22'");
    sql("UPDATE `cache_location` SET `adm3`='Luxembourg' WHERE `code3`='BE34'");
}


// When adding new mutations, take care that they behave well if run multiple
// times. This improves robustness of database versioning.
//
// Please carefully decide if a new mutation relies on any triggers.
// If so, check if triggers need to be updated first - they may have changed
// since the last trigger update mutation (like #113) - or emulate the trigger
// behaviour by additional SQL statements which restore table consistency.
//
// Trigger updates can be directly included in a mutation, or can be done via
// a separate trigger update mutation (see #113 and maintain-113.inc.php).
// See also http://wiki.opencaching.de/index.php/Entwicklung/Datenbankversionierung.
