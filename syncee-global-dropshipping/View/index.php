<?php
/**
 * Syncee admin page template. Rendered inside the WP admin shell via
 * Syncee::synceeMenu(); MUST NOT include outer <html>/<body>.
 *
 * Expects $img_url in scope (set by the caller).
 *
 * @var string $img_url Trailing-slash URL of the plugin's img/ directory.
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap syncee-admin">
    <h1 class="screen-reader-text"><?php esc_html_e('Syncee', 'syncee'); ?></h1>

    <div class="syncee-logo-container">
        <img
            src="<?php echo esc_url($img_url . 'syncee-logo-600x.png'); ?>"
            alt="<?php esc_attr_e('Syncee', 'syncee'); ?>"
            class="syncee-logo"
            id="syncee-logo"
        >
    </div>

    <div id="requirementsTable"></div>

    <div id="registerToWoocommerce" class="syncee-step">
        <h2><?php esc_html_e('Sign up Syncee to WooCommerce', 'syncee'); ?></h2>
        <p><?php esc_html_e('You have to allow WooCommerce access for Syncee.', 'syncee'); ?></p>
        <p>
            <button id="registerToWoocommerceButton" type="button" class="button button-primary button-hero">
                <?php esc_html_e('Sign up Syncee to WooCommerce', 'syncee'); ?>
            </button>
        </p>
    </div>

    <div id="registerToSyncee" class="syncee-step">
        <h2><?php esc_html_e('Sign up to Syncee', 'syncee'); ?></h2>
        <p><?php esc_html_e('You have to sign up to Syncee.', 'syncee'); ?></p>
        <p>
            <button id="registerToSynceeButton" type="button" class="button button-primary button-hero">
                <?php esc_html_e('Sign up to Syncee', 'syncee'); ?>
            </button>
        </p>
    </div>

    <div id="openSyncee" class="syncee-step">
        <div class="notice notice-success inline">
            <p><?php esc_html_e('Your store has been successfully connected to your Syncee account.', 'syncee'); ?></p>
        </div>
        <p class="syncee-actions">
            <button id="openSynceeButton" type="button" class="button button-primary button-hero">
                <?php esc_html_e('Go to Syncee', 'syncee'); ?>
            </button>
            <button id="uninstallEcomButton" type="button" class="button button-link-delete">
                <?php esc_html_e('Disconnect from Syncee', 'syncee'); ?>
            </button>
        </p>
    </div>

    <p class="syncee-refresh">
        <button id="refreshButton" type="button" class="button">
            <?php esc_html_e('Refresh', 'syncee'); ?>
        </button>
    </p>

    <p class="syncee-support">
        <?php
        printf(
            /* translators: %s: support email link */
            esc_html__('If you have any questions or need assistance, contact the Syncee team at %s.', 'syncee'),
            '<a href="mailto:support@syncee.co">support@syncee.co</a>'
        );
        ?>
    </p>

    <p class="syncee-help-links">
        <a target="_blank" rel="noopener" href="https://help.syncee.co/en/articles/5074038-how-to-install-syncee-to-your-wordpress-store-woocommerce-integration">
            <?php esc_html_e('Integration guide', 'syncee'); ?>
        </a>
        <span aria-hidden="true">&nbsp;|&nbsp;</span>
        <a target="_blank" rel="noopener" href="https://help.syncee.co/en/articles/6294863-woocommerce-system-requirements">
            <?php esc_html_e('Requirements', 'syncee'); ?>
        </a>
    </p>
</div>
