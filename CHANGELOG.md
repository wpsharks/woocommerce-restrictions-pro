## v160629.61863

- Updating to latest WPSC.
- Updating Git dotfiles.
- Branch rename; `000000-dev` now just `dev`.
- Adding `current_user_option=""` attribute to `[if /]` shortcode.
- Adding `current_user_meta=""` attribute to `[if /]` shortcode.

## v160625.61236

- Future-proofing `WC_Product` usage in code.

## v160624.34191

- Rebranding. Adding `woocommerce-` prefix.

## v160624.32326

- Updating to latest WPSC.
- Bug fix. `woocommerce_order_given` is an action not a filter.

## v160611.60015

- Moving Order Item utilities to the WPSC.
- Refactor order item meta hook. Deepening the integration to cover a larger array of WC extensions.
- Adding support for [WooCommerce Give Products](https://www.woothemes.com/products/woocommerce-give-products/) extension.

## v160608.43226

- Updating to the latest WPSC.

## v160606.80145-RC

- Updating to the latest WPSC.

- Changing the Restriction access prefix in conditionals from `access_res_` to `access_pkg_` as this more accurately describes what is being tested. The `pkg` portion of this suggests you are testing for access to more than one thing, which is exactly the case; i.e., testing for access to an entire Restriction that may offer access in the form of a package. The `access_pkg_` prefix works better with most of the code samples I have been working with; e.g., `[if current_user_can="access_pkg_pro"]` seems better than the term Restriction in the context of what a customer can do.

## v160601.62250-RC

- Bumping to WPSC minimum: `v160601.61851`.
- Adding custom installation notice template.
- Now collecting license key on install via WPSC updater. However, the license key is not functional yet as there is no API that can authenticate it. Coming soon.

## 160528.40012

- Updating to latest WP Sharks core.
- Adding support for WC logging and WC log files.
- Enhance debugging utilities and log file generation.
- Enhancing Restriction UI. See: https://github.com/websharks/s2member-x/issues/3
- Bug fix. `E_NOTICE` level error whenever WC Subscriptions was missing and Order was saved.

## v160524

- Initial alpha release.
