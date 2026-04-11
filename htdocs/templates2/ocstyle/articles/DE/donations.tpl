{***************************************************************************
* You can find the license in the docs directory
***************************************************************************}
    <div class="content2-pagetitle">
        <img src="resource2/{$opt.template.style}/images/misc/32x32-checked.png" style="margin-right: 10px;" width="32" height="32" alt="" />So kannst Du OpenCaching unterst&uuml;tzen
    </div>
    <div class="content-txtbox-noshade" style="padding-right: 25px;">

            <p>Wir freuen uns, dass Du die Spenden-Seite aufgerufen hast. Neben tatkr&auml;ftigen, helfenden H&auml;nden im Freiwilligen-Team <b>Opencaching.de</b> k&ouml;nnen wir zur Sicherung und Erweiterung des f&uuml;r Euch v&ouml;llig kostenfreien
            Angebotes Eure Unterst&uuml;tzung gut gebrauchen.</p>

            {assign var="donation_target" value=1500}
            {assign var="donation_current" value=442}
            {assign var="donation_last_updated" value="2026-04-11"}
            {assign var="donation_percent" value=$donation_current/$donation_target*100}

            <p>Im Jahr 2026 belaufen sich die laufenden Kosten auf ca. {$donation_target} &euro;, w&auml;hrend die Spenden derzeit erst {$donation_current} &euro; betragen.</p>

            <div style="width: 100%; max-width: 500px; background-color: #eee; border-radius: 4px; margin: 20px 0 10px 0; height: 30px; position: relative; border: 1px solid #ccc;">
                <div style="width: {$donation_percent}%; background-color: #5890a8; height: 100%; border-radius: 3px 0 0 3px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                    {$donation_current} &euro;
                </div>
            </div>
            <div style="width: 100%; max-width: 500px; display: flex; justify-content: space-between; font-size: 0.9em; margin-bottom: 20px;">
                <span>0 &euro;</span>
                <span style="font-style: italic;">Stand vom {$donation_last_updated}</span>
                <span>{$donation_target} &euro;</span>
            </div>

            <p>Mit Eurer freundlichen Spende sorgt Ihr vor allem daf&uuml;r, dass wir das Webhosting der Opencaching-Angebote finanzieren k&ouml;nnen.</p>

            <div style="float: right; margin-right: 30px; ">
                <img src="resource2/misc/donation/globi_danke.png" width="320" height="316" alt="Danke!" />
            </div>
            <h1>Per &Uuml;berweisung spenden:</h1>
            <p>Bank: Volksbank Heiden<br />
            Kontoinhaber: Opencaching Deutschland e.V.<br />
            IBAN: DE07 4286 1608 0094 4853 00<br />
            BIC (SWIFT): GENODEM1HEI</p>

            <h1>Mit Wero spenden:</h1>

            <p>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&amp;data=https://www.youtube.com/watch?v=dQw4w9WgXcQ" width="150" height="150" alt="QR Code" title="Spenden mit Wero" />
            </p>

            {banner_list}
    </div>

