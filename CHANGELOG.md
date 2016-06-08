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
