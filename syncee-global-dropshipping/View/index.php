<?php
?>


<html lang="en">
<header>
    <style>
        .syncee-logo-container {
            width: 300pt;
        }

        .syncee-logo {
            width: 200pt;
            margin: 25px;

        }

        .syncee-table {
            border-collapse: collapse;
        }

        .syncee-td, .syncee-th {
            border: 1px solid #999;
            padding: 0.5rem;
            text-align: left;
        }

        .syncee-body {

        }

        .syncee-button {
            display: inline-block;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            text-align: center;
            text-decoration: none;
            vertical-align: middle;
            cursor: pointer;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
            background-color: transparent;
            border: 0 solid transparent;
            border-radius: 100px;
            font-size: 14px;
            padding: 0 36px;
            min-height: 36px;
        }

        .syncee-button-secondary {
            color: #fff;
            background-color: #0d6efd;
            /*border-color: #0d6efd;*/
        }

        .syncee-button-warning {
            color: #EF5350;
            background-color: #FFCDD2;
            /*border-color: #FFCDD2;*/
        }

        #registerToWoocommerce, #registerToSyncee, #openSyncee {
            display: none;
        }

    </style>
</header>

<body class="syncee-body">

<div class="syncee-logo-container">
    <img src="" alt="syncee logo" class="syncee-logo" id="syncee-logo">
</div>
<div id="requirementsTable"></div>

<div>
    <div>


        <div id="registerToWoocommerce">
            <h2>Sign up Syncee to Woocommerce</h2>
            <p>You have to allow Woocommerce access for Syncee.</p>
            <button id="registerToWoocommerceButton" class="syncee-button syncee-button-secondary">Sign up Syncee to
                Woocommerce
            </button>
            <br>
            <br>
        </div>


        <div id="registerToSyncee">
            <h2>Sign up to Syncee</h2>
            <p>You have to sign up to the Syncee.</p>
            <button id="registerToSynceeButton" class="syncee-button syncee-button-secondary">Sign up to Syncee</button>
            <br>
            <br>
        </div>


        <div id="openSyncee">
            <p>Your store has been successfully connected to your Syncee account.</p>
            <button id="openSynceeButton" class="syncee-button syncee-button-secondary">Go to Syncee</button>
            <button id="uninstallEcomButton" class="syncee-button syncee-button-warning">Disconnect from Syncee</button>
            <br>
            <br>
        </div>


        <div id="refresh">
            <button id="refreshButton" class="syncee-button">Refresh</button>
            <br>
            <br>
        </div>


        <div id="support-team">
            <br>
            <p>If you have any questions or need assistance, contact the Syncee team at support@syncee.co</p>
            <br>

        </div>
        <div>
            <a target="_blank" href="https://help.syncee.co/en/articles/5074038-how-to-install-syncee-to-your-wordpress-store-woocommerce-integration" class="syncee-button">Integration</a>
            <a target="_blank"  href="https://help.syncee.co/en/articles/6294863-woocommerce-system-requirements" class="syncee-button">Requirements</a>
        </div>

    </div>
</div>
</body>
</html>
















