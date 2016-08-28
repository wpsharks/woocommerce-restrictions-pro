## v160828.49771

- Bug fix. Security gate expanded to now cover archive views and other non-singular queries.
- Options UI. Removing configurable option `security_gate_redirect_to_args_enable` in favor of a filter.
- Adding `security_gate_redirect_arg_name` filter.
- Adding `$history` arg to secondary install handler.

## v160801.34929

- Adding a configurable options page. See: **Dashboard → Restrictions → Options**
- Refactored Product/Restriction methods related to meta keys. Now taking advantage of WP Sharks Core utilities.
- Reduced complexity of codebase by removing several classes where the only reason for it existing was to reference a WooCommerce post type.

## v160731.38265

- Tested for compatibility against WordPress v4.6.
- Bug fix. Editors should be granted permission to create Restrictions by default.
- Bug fix. The uninstall handler now removes the Restriction Taxonomy Terms automatically as it should.

## v160727.6263

- Updating to the latest WP Sharks Core.
- Enhancing docBlocks throughout the source code.
- Updating template files. Use `$this->vars` instead of `$this->current_vars`.

## v160718.71135

- Updating to the latest WP Sharks Core v160718.53795.
- Adding `REQUIRES.md` file to list WooCommerce as a formal requirement.

## v160715.32027

- Updating to latest WPS Core.

## v160713.41153

- Updating to the latest WP Sharks Core.

## v160707.3144

- Pulling the `[if /]` shortcode out into a separate plugin before official release.

## v160701.35116

- Bug fix. Use `WC_Product->get_id()` instead of `->id` property, which may not return the correct ID for variations.

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
