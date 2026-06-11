jQuery(document).ready(function () {


    function getCurrentURL() {
        return window.location.href
    }

    if (getCurrentURL().includes('wp-admin') && getCurrentURL().includes('syncee')) {


        jQuery("#btnGenerate").click(generateTable);

        let synceePluginRestUrl = syncee_globals.rest_url;
        let synceePluginSiteUrl = syncee_globals.site_url;
        // Sensitive values are no longer localized; getDataForFrontend populates them.
        let syncee_access_token = null;
        let syncee_user_token = null;
        let dataToSynceeInstaller = null;
        let installerCallbackUrl = syncee_globals.syncee_installer_url + '/woocommerce_auth/callback?';
        let synceeRedirect = syncee_globals.syncee_url + '/crosslogin?token=';
        let synceeV7Redirect = syncee_globals.syncee_installer_url + '/woocommerce_auth/login-with-token?token=';
        let synceeRetailerNonce = syncee_globals.syncee_retailer_nonce;


        let registerToWoocommerce = jQuery('#registerToWoocommerce');
        let registerToSyncee = jQuery('#registerToSyncee');
        let openSyncee = jQuery('#openSyncee');
        let container = jQuery('.container');

        let passed = false;


        function startInstallFlow(stage) {
            return jQuery.ajax({
                url: synceePluginRestUrl + 'startInstallFlow',
                type: 'POST',
                dataType: 'json',
                data: { stage: stage },
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', synceeRetailerNonce);
                }
            }).then(function (result) {
                if (!result || !result.success || !result.data || !result.data.state) {
                    throw new Error('startInstallFlow failed');
                }
                return result.data.state;
            });
        }

        function connectedToSyncee() {
            // V5 retailers populate syncee_access_token; V7 retailers populate
            // syncee_user_token. Either is sufficient to consider the store
            // connected to Syncee.
            return syncee_access_token || syncee_user_token;
        }


        jQuery("#registerToWoocommerceButton").click(function () {
            startInstallFlow('woocommerce_auth').then(function (state) {
                var callbackUrl = synceePluginSiteUrl + '/wp-json/syncee/retailer/v1/callbackFromWoocommerce?state=' + encodeURIComponent(state);
                var returnUrl = synceePluginSiteUrl + '/wp-admin/admin.php?page=syncee';
                window.open(
                    synceePluginSiteUrl + '/wc-auth/v1/authorize'
                    + '?app_name=Syncee'
                    + '&scope=read_write'
                    + '&user_id=1'
                    + '&return_url=' + encodeURIComponent(returnUrl)
                    + '&callback_url=' + encodeURIComponent(callbackUrl)
                );
            }, function () {
                swal('Failed!', 'Could not start the WooCommerce auth flow. Please refresh and try again.', 'warning');
            });
        });


        jQuery("#registerToSynceeButton").click(function () {
            if (!dataToSynceeInstaller) {
                swal('Failed!', 'Missing WooCommerce credentials. Please complete the WooCommerce auth step first.', 'warning');
                return;
            }
            startInstallFlow('syncee_install').then(function (state) {
                window.open(installerCallbackUrl + jQuery.param(dataToSynceeInstaller) + '&state=' + encodeURIComponent(state));
            }, function () {
                swal('Failed!', 'Could not start the Syncee install flow. Please refresh and try again.', 'warning');
            });
        });


        jQuery("#openSynceeButton").click(function () {
            if (connectedToSyncee()) {
                if (syncee_user_token) {
                    window.open(synceeV7Redirect + syncee_user_token);
                } else {
                    window.open(synceeRedirect + syncee_access_token);
                }
            } else {
                swal("Failed!", "Something went wrong!", "warning");
            }
        });

        jQuery("#refreshButton").click(function () {
            hideAllField();
            getRequirements();
        });

        jQuery("#uninstallEcomButton").click(function () {
            uninstallEcom();
        });


        function getRequirements() {
            // Sequence the two calls: requirements first (sets `passed`),
            // then data for frontend (which uses `passed` inside its success
            // handler). Running them in parallel races: if data resolves
            // first, checkInstalledSyncee sees passed=false and shows nothing.
            jQuery.ajax({
                url: synceePluginRestUrl + 'getRequirements',
                type: "get",
                dataType: 'json',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', synceeRetailerNonce);
                },
                success: function (result) {
                    checkRequirements(result.data);
                    getDataForFrontend();
                }
            });
        }


        function getDataForFrontend() {
            jQuery.ajax({
                url: synceePluginRestUrl + 'getDataForFrontend',
                type: "get",
                dataType: 'json',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', synceeRetailerNonce);
                },
                success: function (result) {
                    dataToSynceeInstaller = result.data.data_to_syncee_installer;
                    synceePluginRestUrl = result.data.rest_url;
                    synceePluginSiteUrl = result.data.site_url;
                    syncee_access_token = result.data.syncee_access_token;
                    syncee_user_token = result.data.syncee_user_token;

                    checkInstalledSyncee();

                }
            });
        }

        function uninstallEcom() {

            swal({
                title: "Are you sure?",
                text: "Once deleted, you will not be able to reach your Syncee account!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
                .then((willDelete) => {
                    if (willDelete) {
                        swal("Your Syncee connection has been deleted!");
                        sendUninstallRequest();
                    }
                });


        }

        function sendUninstallRequest() {
            jQuery.ajax({
                url: synceePluginRestUrl + 'uninstallEcom',
                type: "post",
                dataType: 'json',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', synceeRetailerNonce);
                },
                success: function (result) {
                    swal("Success!", "Your Syncee account has been deleted!", "success");
                    getRequirements();
                },
                error: function (e) {
                    swal("Failed!", "Something went wrong! Maybe your account has already been deleted.", "warning");
                    getRequirements();
                }
            });
        }

        function checkRequirements(requirements) {
            let checkedRequirements = 0;
            let requirementsLength = 0;
            jQuery.each(requirements, function (key, value) {
                if (value.pass)
                    checkedRequirements++;
                requirementsLength++;

            });

            passed = checkedRequirements === requirementsLength;

            if (!passed) {
                generateTable(requirements);
                hideAllField();
            } else {
                hideRequirementsTable();
            }

            showContainer();
        }

        function hideRequirementsTable() {
            jQuery('#requirementsTable').css("display", "none");
        }

        function checkInstalledSyncee() {
            hideAllField();

            if (passed)
                if (syncee_access_token || syncee_user_token) {
                    showOpenSyncee();
                } else if (dataToSynceeInstaller) {
                    showRegisterToSyncee();
                } else {
                    showRegisterToWoocommerce();
                }

        }

        function hideAllField() {
            hideOpenSyncee();
            hideRegisterToSyncee();
            hideRegisterToWoocommerce();
        }


        function hideOpenSyncee() {
            openSyncee.css("display", "none");
        }

        function showOpenSyncee() {
            openSyncee.css("display", "block");
        }

        function hideRegisterToSyncee() {
            registerToSyncee.css("display", "none");
        }

        function showRegisterToSyncee() {
            registerToSyncee.css("display", "block");
        }

        function hideRegisterToWoocommerce() {
            registerToWoocommerce.css("display", "none");
        }

        function showRegisterToWoocommerce() {
            registerToWoocommerce.css("display", "block");
        }

        function hideContainer() {
            container.css("display", "none");
        }

        function showContainer() {
            container.css("display", "block");
        }


        function generateTable(requirementsData) {
            //Build an array containing Customer records.
            var statusTable = [];
            statusTable.push(["Component", "Status", "How to fix"]);


            jQuery.each(requirementsData, function (key, value) {

                statusTable.push([value.title, value.pass ? '<span style="color: green">Passed.</span>' : '<span style="color:red;">Failed!</span>', value.pass ? '/' : value.solution])

            });

            //Create a HTML Table element.
            var table = jQuery("<table class='syncee-table' />");
            table[0].border = "1";

            //Get the count of columns.
            var columnCount = statusTable[0].length;

            //Add the header row.
            var row = jQuery(table[0].insertRow(-1));
            for (var i = 0; i < columnCount; i++) {
                var headerCell = jQuery("<th class='syncee-th'/>");
                headerCell.html(statusTable[0][i]);
                row.append(headerCell);
            }

            //Add the data rows.
            for (var i = 1; i < statusTable.length; i++) {
                row = jQuery(table[0].insertRow(-1));
                for (var j = 0; j < columnCount; j++) {
                    var cell = jQuery("<td class='syncee-td' />");
                    cell.html(statusTable[i][j]);
                    row.append(cell);
                }
            }

            var requirementsTable = jQuery("#requirementsTable");
            requirementsTable.html("");
            requirementsTable.append(table);
        }


        hideContainer();

        getRequirements();

        hideAllField();

        // Auto-refresh every 10s so the admin sees the connected state after
        // returning from the Syncee installer without having to click Refresh.
        // Skip when the tab is hidden to avoid burning requests on an idle tab.
        setInterval(function () {
            if (document.visibilityState !== 'hidden') {
                getRequirements();
            }
        }, 10000);
    }

});
