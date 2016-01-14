<?php
/**
 * Media/Files.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\Media
 * @since 120318
 */
namespace s2member
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * Media/Files.
		 *
		 * @package s2Member\Media
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class media extends framework
		{
			/**
			 * Handles initialization routines.
			 *
			 * @attaches-to WordPress® `wp` hook.
			 * @hook-priority `-1` Before most everything else.
			 */
			public function wp()
				{
					if($this->is_file()) $this->serve();
				}

			/**
			 * Serves media files (based on storage engine).
			 *
			 * @return boolean FALSE if unable to serve the file.
			 */
			public function serve()
				{
					if(!($file = $this->is_file())) return FALSE; // NOT a media file.

					$inline = $this->©string->is_true($this->©vars->_REQUEST($this->var_name('inline')));
					$remote = $this->©string->is_true($this->©vars->_REQUEST($this->var_name('remote')));
					$stream = $this->©string->is_true($this->©vars->_REQUEST($this->var_name('stream')));

					if(is_string($secure = $this->©vars->_REQUEST($this->var_name('secure'))))
						$secure = $this->©string->is_true($secure); // Defined explicitly.
					else $secure = is_ssl(); // If NOT set, we auto-detect its value.

					if(!$this->©string->¤is_not_empty($storage = $this->©vars->_REQUEST($this->var_name('storage'))))
						$storage = $this->©options->get('media.default_storage');

					// NO need to check passtag restrictions here; they've already been checked by this time.

					switch($storage) // Serve media file differently (based on storage engine).
					{
						case 's3': // Via Amazon® S3 storage?
								return $this->serve_via_amazon_s3(get_defined_vars());

						case 'cf': // Via Amazon® CloudFront™ storage?
								return $this->serve_via_amazon_cf(get_defined_vars());

						case 'local': // Local?
						default: // Also our default case handler.
							return $this->serve_locally(get_defined_vars());
					}
				}

			/**
			 * Builds a media file variable name.
			 *
			 * @param string $name The media file variable name we need to return here.
			 *
			 * @return string Media file variable name.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$name`` is empty.
			 */
			public function var_name($name)
				{
					$this->check_arg_types('string:!empty', func_get_args());

					return $this->___instance_config->plugin_var_ns.'_media_'.$this->©string->with_underscores($name);
				}

			/**
			 * Builds a rewrite specification name.
			 *
			 * @param string $name The rewrite specification name we need to return here.
			 *
			 * @return string Rewrite specification name.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$name`` is empty.
			 */
			public function rewrite_spec_name($name)
				{
					$this->check_arg_types('string:!empty', func_get_args());

					return $this->___instance_config->plugin_var_ns_with_dashes.'-media-'.$this->©string->with_dashes($name);
				}

			/**
			 * Is a media file being requested?
			 *
			 * @return string The file being requested, else an empty string.
			 */
			public function is_file()
				{
					if(isset($this->cache[__FUNCTION__]))
						return $this->cache[__FUNCTION__];

					$this->cache[__FUNCTION__] = ''; // Default value.

					$file = $this->©vars->_REQUEST($this->var_name('file'));
					if($this->©string->is_not_empty($file) && ($file = $this->sanitize_file($file)))
						$this->cache[__FUNCTION__] = $file;

					return $this->cache[__FUNCTION__];
				}

			/**
			 * Path to a file (in the protected media directory).
			 *
			 * @param null|string $file File, relative to the protected media directory.
			 *
			 * @return string Absolute path to a file (in the protected media directory).
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$file`` is empty (and is NOT NULL).
			 */
			public function abs_file_path($file = NULL)
				{
					$this->check_arg_types(array('null', 'string:!empty'), func_get_args());

					if(is_null($file) && !($file = $this->is_file()))
						throw $this->©exception( // This should NOT happen.
							$this->method(__FUNCTION__).'#unable_to_acquire_file', get_defined_vars(),
							$this->i18n('Unable to acquire `$file`.').
							' '.sprintf($this->i18n('Got: `%1$s`.'), $file)
						);
					return $this->©dir->private_media().'/'.$this->sanitize_file($file);
				}

			/**
			 * Checks/sanitizes a file specification (i.e. relative path).
			 *
			 * @param null|string $file File, relative to the protected media directory.
			 *
			 * @return string File, relative to the protected media directory (possible empty string).
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$file`` is empty (and is NOT NULL).
			 * @throws exception If ``$file`` begins with a dot, or if it contains double dots.
			 * @throws exception If ``$file`` is empty after having been sanitized here.
			 */
			public function sanitize_file($file = NULL)
				{
					$this->check_arg_types(array('null', 'string:!empty'), func_get_args());

					if(is_null($file) && !($file = $this->is_file()))
						throw $this->©exception( // This should NOT happen.
							$this->method(__FUNCTION__).'#unable_to_acquire_file', get_defined_vars(),
							$this->i18n('Unable to acquire `$file`.').
							' '.sprintf($this->i18n('Got: `%1$s`.'), $file)
						);
					$file = ltrim($this->©dir->n_seps($file), '/'); // Force relative.

					if(!strlen($file))
						throw $this->©exception(
							$this->method(__FUNCTION__).'#empty_file', get_defined_vars(),
							$this->i18n('Empty `$file` specification.')
						);
					if(strpos($file, '.') === 0 || strpos($file, '/.') !== FALSE)
						throw $this->©exception(
							$this->method(__FUNCTION__).'#dot_files', get_defined_vars(),
							$this->i18n('Invalid `$file` specification. Files may NOT begin with a dot: `.`.').
							' '.sprintf($this->i18n('Got: `%1$s`.'), $file)
						);
					if(strpos($file, '..') !== FALSE)
						throw $this->©exception(
							$this->method(__FUNCTION__).'#double_dots', get_defined_vars(),
							$this->i18n('Invalid `$file` specification. Files may NOT contain double dots: `..`.').
							' '.sprintf($this->i18n('Got: `%1$s`.'), $file)
						);
					return $file; // Sanitized file specification.
				}

			/**
			 * Gets a media file permalink.
			 *
			 * @param null|string $file File, relative to the protected media directory.
			 *
			 * @param array       $args Optional. Additional arguments (but only those which are supported here).
			 *    Supported arguments include: `inline`, `remote`, `stream` and `secure` (all boolean values).
			 *
			 *    Also supported here, is the `storage` argument (a string value indicates which storage engine to serve from).
			 *       Supported storage engines include: `local` (default), `s3` for Amazon® S3, or `cf` for Amazon® Cloudfront™.
			 *
			 * @param boolean     $rewrite Optional. Defaults to a TRUE value.
			 *    By default, we use ModRewrite linkage for the best compatibility across various media playback devices.
			 *    However, this can be set to a FALSE value, to disable ModRewrite linkage, and instead we'll use name/value pairs.
			 *
			 * @return string A permalink to this media file.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$file`` is empty (and is NOT NULL).
			 */
			public function get_permalink($file = NULL, $args = array(), $rewrite = TRUE)
				{
					$this->check_arg_types(array('null', 'string:!empty'), 'array', 'boolean', func_get_args());

					if(is_null($file) && !($file = $this->is_file()))
						throw $this->©exception( // This should NOT happen.
							$this->method(__FUNCTION__).'#unable_to_acquire_file', get_defined_vars(),
							$this->i18n('Unable to acquire `$file`.').
							' '.sprintf($this->i18n('Got: `%1$s`.'), $file)
						);
					$file = $this->sanitize_file($file);

					$default_args = array(
						'inline'  => NULL,
						'remote'  => NULL,
						'stream'  => NULL,
						'secure'  => NULL,
						'storage' => NULL
					);
					$args         = $this->check_extension_arg_types(
						'boolean', 'boolean', 'boolean', 'boolean', 'string:!empty', $default_args, $args
					);
					if(isset($args['inline']))
						{
							$pairs[$this->var_name('inline')] = ($args['inline']) ? 'yes' : 'no';
							$rewrite_specs[]                  = $this->rewrite_spec_name('inline').(($args['inline']) ? '' : '-no');
						}
					if(isset($args['remote']))
						{
							$pairs[$this->var_name('remote')] = ($args['remote']) ? 'yes' : 'no';
							$rewrite_specs[]                  = $this->rewrite_spec_name('remote').(($args['remote']) ? '' : '-no');
						}
					if(isset($args['stream']))
						{
							$pairs[$this->var_name('stream')] = ($args['stream']) ? 'yes' : 'no';
							$rewrite_specs[]                  = $this->rewrite_spec_name('stream').(($args['stream']) ? '' : '-no');
						}
					if(isset($args['secure']))
						{
							$pairs[$this->var_name('secure')] = ($args['secure']) ? 'yes' : 'no';
							$rewrite_specs[]                  = $this->rewrite_spec_name('secure').(($args['secure']) ? '' : '-no');
						}
					if(!empty($args['storage']))
						{
							$pairs[$this->var_name('storage')] = $args['storage'];
							$rewrite_specs[]                   = $this->rewrite_spec_name('storage').'-'.$args['storage'];
						}
					$pairs[$this->var_name('file')] = $file; // Last argument (always).
					$rewrite_specs                  = (!empty($rewrite_specs)) ? $rewrite_specs : array();

					if($rewrite) // Using rewrite linkage?
						{
							if($rewrite_specs) // If we have rewrite specs, squeeze them in now.
								$abs_file_path = $this->abs_file_path(implode('/', $rewrite_specs).'/'.$file);
							else $abs_file_path = $this->abs_file_path($file);

							return $this->©url->to_wp_abs_dir_file($abs_file_path);
						}
					return add_query_arg(urlencode_deep($pairs), $this->©url->to_wp_home_uri());
				}

			/**
			 * URL to a protected media file (via Amazon® S3 storage).
			 *
			 * @param array   $args Required. Argument values.
			 *    Argument (string)`file` is the ONLY required argument value.
			 *    Optional: `inline`, `remote`, `stream`, `secure` (all boolean values).
			 *    Optional: `user` in case we need to limit access to a particular user.
			 *
			 * @param boolean $check_passtag_restrictions Optional. Defaults to a FALSE value.
			 *    This is FALSE by default, because passtag restrictions are normally checked via {@link serve()}.
			 *
			 * @return string URL to a protected media file (via Amazon® S3 storage).
			 *    If checking restrictions (and access is denied for any reason); this returns an empty string.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$args['file']`` is empty.
			 */
			public function get_amazon_s3_url($args = array(), $check_passtag_restrictions = FALSE)
				{
					$this->check_arg_types('array:!empty', 'boolean', func_get_args());

					$default_args = array(
						'file'   => '',
						'inline' => FALSE,
						'remote' => FALSE,
						'stream' => FALSE,
						'secure' => is_ssl(),
						'user'   => NULL
					);
					$args         = $this->check_extension_arg_types(
						'string:!empty', 'boolean', 'boolean', 'boolean', 'boolean', $this->©user_utils->which_types(),
						$default_args, $args, 1 // Only ONE required argument value here.
					);
					$file         = $this->sanitize_file($args['file']);
					$user         = $this->©user_utils->which($args['user']);

					$inline = $args['inline'];
					$remote = $args['remote'];
					$stream = $args['stream'];
					$secure = $args['secure'];

					if($check_passtag_restrictions) // Check passtag restrictions and log?
						if($this->©passtag_restrictions->check_media($file, $user, FALSE, TRUE))
							return ''; // Not allowed. Empty string.

					$basename  = basename($file);
					$extension = $this->©file->extension($file);
					$mime_type = 'application/octet-stream'; // Default MIME type.

					if($extension) // Only if the file has an extension.
						if(($mime_types = $this->©file->mime_types()) && !empty($mime_types[$extension]))
							$mime_type = $mime_types[$extension];

					$bucket     = $this->©options->get('media.amazon.s3.bucket');
					$access_key = $this->©options->get('media.amazon.s3.access_key');
					$secret_key = $this->©options->get('media.amazon.s3.secret_key');

					$expires_time = strtotime('+'.$this->©options->get('media.amazon.s3.expires_offset_time'));

					$meta_data                = array(
						'response-expires'             => date(DATE_RFC1123, strtotime('-1 week')),
						'response-cache-control'       => 'no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0',
						'response-content-disposition' => (($inline) ? 'inline' : 'attachment').';'. // With support for UTF-8 chars.
						                                  ' filename="'.$this->©string->esc_dq($basename).'"; filename*=UTF-8\'\''.rawurlencode($basename),
						'response-content-type'        => $mime_type
					);
					$uri_with_unencoded_query = add_query_arg($meta_data, '/'.str_ireplace('%2F', '/', rawurlencode($file)));
					$uri                      = add_query_arg(rawurlencode_deep($meta_data), '/'.str_ireplace('%2F', '/', rawurlencode($file)));
					$signature                = base64_encode($this->©encryption->hmac_sha1_sign('GET'."\n\n\n".$expires_time."\n".'/'.$bucket.$uri_with_unencoded_query, $secret_key));
					$url                      = 'http'.(($secure) ? 's' : '').'://'.$bucket.'.s3.amazonaws.com'.$uri;

					return add_query_arg(rawurlencode_deep(array('AWSAccessKeyId' => $access_key, 'Expires' => $expires_time, 'Signature' => $signature)), $url);
				}

			/**
			 * URL to a protected media file (via Amazon® CloudFront™ storage).
			 *
			 * @param array   $args Required. Argument values.
			 *    Argument (string)`file` is the ONLY required argument value.
			 *    Optional: `inline`, `remote`, `stream`, `secure` (all boolean values).
			 *    Optional: `user` in case we need to limit access to a particular user.
			 *
			 * @param boolean $check_passtag_restrictions Optional. Defaults to a FALSE value.
			 *    This is FALSE by default, because passtag restrictions are normally checked via {@link serve()}.
			 *
			 * @return string URL to a protected media file (via Amazon® CloudFront™ storage).
			 *    If checking restrictions (and access is denied for any reason); this returns an empty string.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$args['file']`` is empty.
			 */
			public function get_amazon_cf_url($args = array(), $check_passtag_restrictions = FALSE)
				{
					$this->check_arg_types('array:!empty', 'boolean', func_get_args());

					$default_args = array(
						'file'   => '',
						'inline' => FALSE,
						'remote' => FALSE,
						'stream' => FALSE,
						'secure' => is_ssl(),
						'user'   => NULL
					);
					$args         = $this->check_extension_arg_types(
						'string:!empty', 'boolean', 'boolean', 'boolean', 'boolean', $this->©user_utils->which_types(),
						$default_args, $args, 1 // Only ONE required argument value here.
					);
					$file         = $this->sanitize_file($args['file']);
					$user         = $this->©user_utils->which($args['user']);

					$inline = $args['inline'];
					$remote = $args['remote'];
					$stream = $args['stream'];
					$secure = $args['secure'];

					if($check_passtag_restrictions) // Check passtag restrictions and log?
						if($this->©passtag_restrictions->check_media($file, $user, FALSE, TRUE))
							return ''; // Not allowed. Empty string.

					$basename  = basename($file);
					$extension = $this->©file->extension($file);
					$mime_type = 'application/octet-stream'; // Default MIME type.

					if($extension) // Only if the file has an extension.
						if(($mime_types = $this->©file->mime_types()) && !empty($mime_types[$extension]))
							$mime_type = $mime_types[$extension];

					$key_pair_id = $this->©options->get('media.amazon.cf.key_pair_id');
					$private_key = $this->©options->get('media.amazon.cf.private_key');

					$downloads_cname        = $this->©options->get('media.amazon.cf.downloads_cname');
					$downloads_distro       = $this->©options->get('media.amazon.cf.downloads_distro');
					$downloads_cname_distro = ($downloads_cname) ? $downloads_cname : $downloads_distro;

					$streaming_cname        = $this->©options->get('media.amazon.cf.streaming_cname');
					$streaming_distro       = $this->©options->get('media.amazon.cf.streaming_distro');
					$streaming_cname_distro = ($streaming_cname) ? $streaming_cname : $streaming_distro;

					$expires_time = strtotime('+'.$this->©options->get('media.amazon.cf.expires_offset_time'));

					if($stream && $extension && in_array($extension, array('mp3'), TRUE))
						$resource = $this->©file->no_extension($file); // No extension.
					else if($stream) $resource = $file; // Default identifier for streams.
					else // Default identifier for downloads is the full URL that leads to the resource.
						$resource = 'http'.(($secure) ? 's' : '').'://'.$downloads_cname_distro.'/'.str_ireplace('%2F', '/', rawurlencode($file));

					if($stream) // Don't encode ``$file`` because most RTMP players will fail on encoded file names.
						$url = 'rtmp'.(($secure) ? 'e' : '').'://'.$streaming_cname_distro.'/cfx/st/' /* Do NOT encode this. */.$file;
					else $url = 'http'.(($secure) ? 's' : '').'://'.$downloads_cname_distro.'/'.str_ireplace('%2F', '/', rawurlencode($file));

					$policy = array('Statement' =>
						                array( // Array of statements.
						                       array('Resource'  => $resource,
						                             'Condition' => array('IpAddress'    => array('AWS:SourceIp' => $user->ip.'/32'),
						                                                  'DateLessThan' => array('AWS:EpochTime' => $expires_time)))
						                ));
					if($this->©env->is_localhost())
						unset($policy['Statement'][0]['Condition']['IpAddress']);
					$policy = json_encode($policy); // Always JSON encode the policy.

					$signature = $this->©string->base64_url_safe_encode($this->©encryption->rsa_sha1_sign($policy, $private_key), array('+', '=', '/'), array('-', '_', '~'), '');
					$policy    = $this->©string->base64_url_safe_encode($policy, array('+', '=', '/'), array('-', '_', '~'), '');

					return add_query_arg(rawurlencode_deep(array('Key-Pair-Id' => $key_pair_id, 'Policy' => $policy, 'Signature' => $signature)), $url);
				}

			/**
			 * Serves a protected media file (via Amazon® S3 storage).
			 *
			 * @param array $args Required. Argument values.
			 *    Argument (string)`file` is the ONLY required argument value.
			 *    Optional: `inline`, `remote`, `stream`, `secure` (all boolean values).
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$args['file']`` is empty, or does NOT exist locally.
			 * @throws exception If unable to serve the file for any reason.
			 */
			public function serve_via_amazon_s3($args = array())
				{
					$this->check_arg_types('array:!empty', func_get_args());

					$amazon_s3_url = $this->get_amazon_s3_url($args);

					$this->do_action('before_'.__FUNCTION__, get_defined_vars());

					wp_redirect($amazon_s3_url).exit();
				}

			/**
			 * Serves a protected media file (via Amazon® CloudFront™ storage).
			 *
			 * @param array $args Required. Argument values.
			 *    Argument (string)`file` is the ONLY required argument value.
			 *    Optional: `inline`, `remote`, `stream`, `secure` (all boolean values).
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$args['file']`` is empty, or does NOT exist locally.
			 * @throws exception If unable to serve the file for any reason.
			 */
			public function serve_via_amazon_cf($args = array())
				{
					$this->check_arg_types('array:!empty', func_get_args());

					$amazon_cf_url = $this->get_amazon_cf_url($args);

					$this->do_action('before_'.__FUNCTION__, get_defined_vars());

					wp_redirect($amazon_cf_url).exit();
				}

			/**
			 * Serves a protected media file (via local storage).
			 *
			 * @param array $args Required. Argument values.
			 *    Argument (string)`file` is the ONLY required argument value.
			 *    Optional: `inline`, `remote`, `stream`, `secure` (all boolean values).
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 * @throws exception If ``$args['file']`` is empty, or does NOT exist locally.
			 * @throws exception If unable to serve the file for any reason.
			 */
			public function serve_locally($args = array())
				{
					$this->check_arg_types('array:!empty', func_get_args());

					$default_args  = array(
						'file'   => '',
						'inline' => FALSE,
						'remote' => FALSE,
						'stream' => FALSE,
						'secure' => is_ssl()
					);
					$args          = $this->check_extension_arg_types(
						'string:!empty', 'boolean', 'boolean', 'boolean', 'boolean', $default_args, $args
					);
					$file          = $this->sanitize_file($args['file']);
					$abs_file_path = $this->abs_file_path($file);

					$inline = $args['inline'];
					$remote = $args['remote'];
					$stream = $args['stream'];
					$secure = $args['secure'];

					if(!$file || !is_file($abs_file_path))
						throw $this->©exception(
							$this->method(__FUNCTION__).'#missing_file', get_defined_vars(),
							sprintf($this->translate('File path NOT found locally: `%1$s`.'), $abs_file_path)
						);
					$this->©env->maximize_time_memory_limits();
					$this->©env->disable_gzip();

					$filesize  = filesize($abs_file_path);
					$basename  = basename($abs_file_path);
					$extension = $this->©file->extension($abs_file_path);
					$mime_type = 'application/octet-stream'; // Default MIME type.

					if($extension) // Only if the file has an extension.
						if(($mime_types = $this->©file->mime_types()) && !empty($mime_types[$extension]))
							$mime_type = $mime_types[$extension];

					$disposition = (($inline) ? 'inline' : 'attachment').'; filename="'.$this->©string->esc_dq($basename).'"; filename*=UTF-8\'\''.rawurlencode($basename);
					$encoding    = ($this->©options->get('media.use_content_encoding_none')) ? ' none' : '';

					$site_supports_ranges = (boolean)$this->©options->get('media.support_ranges');
					$range                = ($site_supports_ranges) ? (string)$this->©vars->_SERVER('HTTP_RANGE') : '';

					if(!$range && $site_supports_ranges && $this->©function->is_possible('apache_request_headers'))
						// Note: ``apache_request_headers()`` works in FastCGI too, starting w/ PHP v5.4.
						foreach((array)apache_request_headers() as $_header => $_value)
							if(is_string($_header) && strcasecmp($_header, 'range') === 0)
								$range = $_value;
					unset($_header, $_value); // Housekeeping.

					if($range && $site_supports_ranges) // Requesting a specific byte range?
						{
							if(strpos($range, '=') === FALSE) // Invalid range?
								{
									$this->©headers->clean_status_type(416, $mime_type);
									$this->©headers->no_cache(); // No-cache.

									header('Accept-Ranges: bytes');
									header('Content-Encoding:'.$encoding);
									header('Content-Disposition: '.$disposition);
									header('Content-Length: '.$filesize);

									exit(); // Stop here (invalid range).
								}
							list($range_type, $byte_range) = preg_split('/\s*\=\s*/', $range, 2);

							$range_type = strtolower(trim($range_type));
							$byte_range = trim($byte_range);

							if($range_type !== 'bytes') // Invalid range type?
								{
									$this->©headers->clean_status_type(416, $mime_type);
									$this->©headers->no_cache(); // No-cache.

									header('Accept-Ranges: bytes');
									header('Content-Encoding:'.$encoding);
									header('Content-Disposition: '.$disposition);
									header('Content-Length: '.$filesize);

									exit(); // Stop here (invalid range).
								}
							$byte_ranges = preg_split('/\s*,\s*/', $byte_range);

							if(strpos($byte_ranges[0], '-') === FALSE) // Invalid byte range?
								{
									$this->©headers->clean_status_type(416, $mime_type);
									$this->©headers->no_cache(); // No-cache.

									header('Accept-Ranges: bytes');
									header('Content-Encoding:'.$encoding);
									header('Content-Disposition: '.$disposition);
									header('Content-Length: '.$filesize);

									exit(); // Stop here (invalid range).
								}
							list($byte_range_start, $byte_range_stops) = // Only dealing with the first byte range.
								preg_split('/\s*\-\s*/', $byte_ranges[0], 2); // Others are simply ignored here.

							$byte_range_start = trim($byte_range_start);
							$byte_range_stops = trim($byte_range_stops);

							$byte_range_start = ($byte_range_start === '') ? NULL : (integer)$byte_range_start;
							$byte_range_stops = ($byte_range_stops === '') ? NULL : (integer)$byte_range_stops;

							if(!isset($byte_range_start) && $byte_range_stops > 0 && $byte_range_stops <= $filesize)
								{
									$byte_range_start = $filesize - $byte_range_stops;
									$byte_range_stops = $filesize - 1; // The last X number of bytes.
								}
							else if(!isset($byte_range_stops) && $byte_range_start >= 0 && $byte_range_start < $filesize - 1)
								{
									$byte_range_stops = $filesize - 1; // To the end of the file in this case.
								}
							else if(isset($byte_range_start, $byte_range_stops) && $byte_range_start >= 0 && $byte_range_start < $filesize - 1 && $byte_range_stops > $byte_range_start && $byte_range_stops <= $filesize - 1)
								{
									// Nothing to do in this case, starts/stops already defined properly.
								}
							else // We have an invalid byte range (e.g. it did NOT meet any of the conditions above).
								{
									$this->©headers->clean_status_type(416, $mime_type);
									$this->©headers->no_cache(); // No-cache.

									header('Accept-Ranges: bytes');
									header('Content-Encoding:'.$encoding);
									header('Content-Disposition: '.$disposition);
									header('Content-Length: '.$filesize);

									exit(); // Stop here (invalid range).
								}
							// Serving partial content in this range.

							$this->©headers->clean_status_type(206, $mime_type);
							$this->©headers->no_cache(); // No-cache.

							$byte_range_size    = $byte_range_stops - $byte_range_start + 1;
							$content_byte_range = 'bytes '.$byte_range_start.'-'.$byte_range_stops.'/'.$filesize;

							header('Accept-Ranges: bytes');
							header('Content-Encoding:'.$encoding);
							header('Content-Disposition: '.$disposition);
							header('Content-Range: '.$content_byte_range);
							header('Content-Length: '.$byte_range_size);
						}
					else // A normal request (NOT a specific byte range).
						{
							$this->©headers->clean_status_type(200, $mime_type);
							$this->©headers->no_cache(); // No-cache.

							if($site_supports_ranges) // Only if this site DOES support range requests.
								header('Accept-Ranges: bytes'); // Adds support for resumable downloads.
							else header('Accept-Ranges: none'); // Else we say no.

							header('Content-Encoding:'.$encoding);
							header('Content-Disposition: '.$disposition);
							header('Content-Length: '.$filesize);
						}
					$this->do_action('before_'.__FUNCTION__, get_defined_vars());

					if(!is_resource($resource = fopen($abs_file_path, 'rb')))
						throw $this->©exception(
							$this->method(__FUNCTION__).'#resource_failure', get_defined_vars(),
							sprintf($this->translate('Resource failure. Unable to open file path: `%1$s`.'), $abs_file_path)
						);
					if($range && $site_supports_ranges && isset($byte_range_size, $byte_range_start))
						{
							$_bytes_to_read = $byte_range_size;
							fseek($resource, $byte_range_start);
						}
					else $_bytes_to_read = $filesize; // Entire file.

					$chunk_size = (integer)$this->©options->get('media.chunk_size');

					while($_bytes_to_read) // While we have bytes to read here.
						{
							$_reading = ($_bytes_to_read > $chunk_size)
								? $chunk_size : $_bytes_to_read;

							$_bytes_to_read -= $_reading;

							echo fread($resource, $_reading);
							flush(); // Flush bytes to device.
						}
					fclose($resource); // Close file resource handle.
					unset($_bytes_to_read, $_reading); // Housekeeping.

					exit(); // Exit script execution now (the file has been served).
				}

			/**
			 * Adds media rewrite rules into private `.htaccess` file.
			 *
			 * @param boolean $return_rules Optional. This defaults to a FALSE value.
			 *    If this is TRUE, instead of returning a boolean value, we simply return the rules.
			 *
			 * @return boolean|string TRUE if rules were written to the `.htaccess` file, else FALSE by default.
			 *    Or, if ``$return_rules`` is TRUE, we simply return the ``$rules`` constructed by this routine.
			 */
			public function add_media_rules_into_private_htaccess($return_rules = FALSE)
				{
					$this->check_arg_types('boolean', func_get_args());

					$htaccess = $this->©dir->private_media().'/.htaccess';

					if(is_multisite() && !empty($GLOBALS['base']) && is_string($GLOBALS['base']))
						$base = $GLOBALS['base']; // Hard-coded multisite base path.

					else if(($base_path = $this->©url->parse($this->©url->to_wp_network_home_uri(), PHP_URL_PATH)))
						$base = $base_path; // Parsed base path (including support for multisite networks).

					else $base = '/'; // Default value.

					$rules = // Builds `.htaccess` rewrite rules.

						// Breaking this up into multiple chunks because PhpStorm
						// has trouble parsing the entire string at once.

						'Options +FollowSymLinks -Indexes'."\n".

						'<IfModule env_module>'."\n".
						"\t".'SetEnv no-gzip 1'."\n".
						'</IfModule>'."\n";

					$rules .= // Builds `.htaccess` rewrite rules.

						'<IfModule rewrite_module>'."\n".

						"\t".'RewriteEngine On'."\n".
						"\t".'RewriteBase '.$base."\n".

						"\t".'RewriteCond %{ENV:'.$this->var_name('setup').'} !^complete$'."\n".
						"\t".'RewriteRule ^(.*)$ - [E='.$this->var_name('wp_vdir').':0,E='.$this->var_name('file').':$1,E='.$this->var_name('inline').':0,E='.$this->var_name('remote').':0,E='.$this->var_name('stream').':0,E='.$this->var_name('secure').':0,E='.$this->var_name('storage').':0,E='.$this->var_name('setup').':complete]'."\n".

						"\t".'RewriteCond %{ENV:'.$this->var_name('wp_vdir_check').'} !^complete$'."\n".
						"\t".'RewriteCond %{THE_REQUEST} ^(?:GET|HEAD)(?:[\ ]+)(?:'.preg_quote($base, ' ').')([a-zA-Z0-9_\-]+/)(?:wp-content/)'."\n".
						"\t".'RewriteRule ^(.*)$ - [E='.$this->var_name('wp_vdir').':,E='.$this->var_name('wp_vdir').':%1,E='.$this->var_name('wp_vdir_check').':complete]'."\n";

					$rules .= // Builds `.htaccess` rewrite rules.

						"\t".'RewriteCond %{ENV:'.$this->var_name('file').'} ^(.*?)(?:'.preg_quote($this->rewrite_spec_name('inline'), ' ').'/)(.+)$'."\n".
						"\t".'RewriteRule ^(.*)$ - [N,E='.$this->var_name('file').':,E='.$this->var_name('file').':%1%2,E='.$this->var_name('inline').':,E='.$this->var_name('inline').':&'.$this->var_name('inline').'=yes]'."\n".
						"\t".'RewriteCond %{ENV:'.$this->var_name('file').'} ^(.*?)(?:'.preg_quote($this->rewrite_spec_name('inline'), ' ').'-(.+?)/)(.+)$'."\n".
						"\t".'RewriteRule ^(.*)$ - [N,E='.$this->var_name('file').':,E='.$this->var_name('file').':%1%3,E='.$this->var_name('inline').':,E='.$this->var_name('inline').':&'.$this->var_name('inline').'=%2]'."\n".

						"\t".'RewriteCond %{ENV:'.$this->var_name('file').'} ^(.*?)(?:'.preg_quote($this->rewrite_spec_name('remote'), ' ').'/)(.+)$'."\n".
						"\t".'RewriteRule ^(.*)$ - [N,E='.$this->var_name('file').':,E='.$this->var_name('file').':%1%2,E='.$this->var_name('remote').':,E='.$this->var_name('remote').':&'.$this->var_name('remote').'=yes]'."\n".
						"\t".'RewriteCond %{ENV:'.$this->var_name('file').'} ^(.*?)(?:'.preg_quote($this->rewrite_spec_name('remote'), ' ').'-(.+?)/)(.+)$'."\n".
						"\t".'RewriteRule ^(.*)$ - [N,E='.$this->var_name('file').':,E='.$this->var_name('file').':%1%3,E='.$this->var_name('remote').':,E='.$this->var_name('remote').':&'.$this->var_name('remote').'=%2]'."\n".

						"\t".'RewriteCond %{ENV:'.$this->var_name('file').'} ^(.*?)(?:'.preg_quote($this->rewrite_spec_name('stream'), ' ').'/)(.+)$'."\n".
						"\t".'RewriteRule ^(.*)$ - [N,E='.$this->var_name('file').':,E='.$this->var_name('file').':%1%2,E='.$this->var_name('stream').':,E='.$this->var_name('stream').':&'.$this->var_name('stream').'=yes]'."\n".
						"\t".'RewriteCond %{ENV:'.$this->var_name('file').'} ^(.*?)(?:'.preg_quote($this->rewrite_spec_name('stream'), ' ').'-(.+?)/)(.+)$'."\n".
						"\t".'RewriteRule ^(.*)$ - [N,E='.$this->var_name('file').':,E='.$this->var_name('file').':%1%3,E='.$this->var_name('stream').':,E='.$this->var_name('stream').':&'.$this->var_name('stream').'=%2]'."\n".

						"\t".'RewriteCond %{ENV:'.$this->var_name('file').'} ^(.*?)(?:'.preg_quote($this->rewrite_spec_name('secure'), ' ').'/)(.+)$'."\n".
						"\t".'RewriteRule ^(.*)$ - [N,E='.$this->var_name('file').':,E='.$this->var_name('file').':%1%2,E='.$this->var_name('secure').':,E='.$this->var_name('secure').':&'.$this->var_name('secure').'=yes]'."\n".
						"\t".'RewriteCond %{ENV:'.$this->var_name('file').'} ^(.*?)(?:'.preg_quote($this->rewrite_spec_name('secure'), ' ').'-(.+?)/)(.+)$'."\n".
						"\t".'RewriteRule ^(.*)$ - [N,E='.$this->var_name('file').':,E='.$this->var_name('file').':%1%3,E='.$this->var_name('secure').':,E='.$this->var_name('secure').':&'.$this->var_name('secure').'=%2]'."\n".

						"\t".'RewriteCond %{ENV:'.$this->var_name('file').'} ^(.*?)(?:'.preg_quote($this->rewrite_spec_name('storage'), ' ').'-(.+?)/)(.+)$'."\n".
						"\t".'RewriteRule ^(.*)$ - [N,E='.$this->var_name('file').':,E='.$this->var_name('file').':%1%3,E='.$this->var_name('storage').':,E='.$this->var_name('storage').':&'.$this->var_name('storage').'=%2]'."\n";

					$rules .= // Builds `.htaccess` rewrite rules.

						"\t".'RewriteCond %{ENV:'.$this->var_name('wp_vdir').'} ^0$'."\n".
						"\t".'RewriteRule ^(.*)$ - [E='.$this->var_name('wp_vdir').':]'."\n".

						"\t".'RewriteCond %{ENV:'.$this->var_name('inline').'} ^0$'."\n".
						"\t".'RewriteRule ^(.*)$ - [E='.$this->var_name('inline').':]'."\n".

						"\t".'RewriteCond %{ENV:'.$this->var_name('remote').'} ^0$'."\n".
						"\t".'RewriteRule ^(.*)$ - [E='.$this->var_name('remote').':]'."\n".

						"\t".'RewriteCond %{ENV:'.$this->var_name('stream').'} ^0$'."\n".
						"\t".'RewriteRule ^(.*)$ - [E='.$this->var_name('stream').':]'."\n".

						"\t".'RewriteCond %{ENV:'.$this->var_name('secure').'} ^0$'."\n".
						"\t".'RewriteRule ^(.*)$ - [E='.$this->var_name('secure').':]'."\n".

						"\t".'RewriteCond %{ENV:'.$this->var_name('storage').'} ^0$'."\n".
						"\t".'RewriteRule ^(.*)$ - [E='.$this->var_name('storage').':]'."\n";

					$rules .= // Builds `.htaccess` rewrite rules.

						"\t".'RewriteRule ^(.*)$ %{ENV:'.$this->var_name('wp_vdir').'}?'.$this->var_name('file').'=%{ENV:'.$this->var_name('file').'}%{ENV:'.$this->var_name('inline').'}%{ENV:'.$this->var_name('remote').'}%{ENV:'.$this->var_name('stream').'}%{ENV:'.$this->var_name('secure').'}%{ENV:'.$this->var_name('storage').'} [QSA,L]'."\n".

						'</IfModule>'."\n";

					$rules .= // Builds `.htaccess` rewrite rules.

						'<IfModule !rewrite_module>'."\n".
						"\t".'deny from all'."\n".
						'</IfModule>';

					if(is_file($htaccess) && is_readable($htaccess) && is_writable($htaccess))
						$file_put_contents = file_put_contents($htaccess, $rules);

					else if(!is_file($htaccess) && is_writable($this->©dir->n_seps_up($htaccess)))
						$file_put_contents = file_put_contents($htaccess, $rules);

					if($return_rules) // Returning rules?
						return $rules; // Useful in some scenarios.

					return (!empty($file_put_contents)) ? TRUE : FALSE;
				}

			/**
			 * Checks for existence of GZIP rules in root `.htaccess` file.
			 *
			 * @return boolean TRUE if GZIP rules are in the `.htaccess` file, else FALSE by default.
			 */
			public function gzip_rules_exist_in_root_htaccess()
				{
					$htaccess   = $this->©dir->n_seps(ABSPATH).'/.htaccess';
					$start_line = '# BEGIN '.$this->___instance_config->plugin_name.' GZIP exclusions';
					$end_line   = '# END '.$this->___instance_config->plugin_name.' GZIP exclusions';

					if(is_file($htaccess) && is_readable($htaccess) && is_string($htaccess_contents = file_get_contents($htaccess)))
						return (preg_match('/'.preg_quote($start_line, '/').'['."\r\n".']+(?s:.+)['."\r\n".']+'.preg_quote($end_line, '/').'['."\r\n".']{0,2}/i', $htaccess_contents)) ? TRUE : FALSE;

					return FALSE; // Default return value.
				}

			/**
			 * Writes GZIP rules into root `.htaccess` file.
			 *
			 * @param boolean $return_rules Optional. This defaults to a FALSE value.
			 *    If this is TRUE, instead of returning a boolean value, we simply return the rules.
			 *
			 * @return boolean TRUE if rules were written to the `.htaccess` file, else FALSE by default.
			 *    Or, if ``$return_rules`` is TRUE, we simply return the ``$rules`` constructed by this routine.
			 */
			public function add_gzip_rules_into_root_htaccess($return_rules = FALSE)
				{
					$htaccess   = $this->©dir->n_seps(ABSPATH).'/.htaccess';
					$start_line = '# BEGIN '.$this->___instance_config->plugin_name.' GZIP exclusions';
					$end_line   = '# END '.$this->___instance_config->plugin_name.' GZIP exclusions';

					if(is_multisite() && !empty($GLOBALS['base']) && is_string($GLOBALS['base']))
						$base = $GLOBALS['base']; // Hard-coded multisite base path.

					else if(($base_path = $this->©url->parse($this->©url->to_wp_network_home_uri(), PHP_URL_PATH)))
						$base = $base_path; // Parsed base path (including support for multisite networks).

					else $base = '/'; // Default value.

					$rules = // Builds our no GZIP rules.
						$start_line."\n".
						'<IfModule rewrite_module>'."\n".
						"\t".'RewriteEngine On'."\n".
						"\t".'RewriteBase '.$base."\n".
						"\t".'RewriteCond %{QUERY_STRING} (^|\?|&)'.preg_quote($this->var_name('file'), ' ').'\=.+ [OR]'."\n".
						"\t".'RewriteCond %{QUERY_STRING} (^|\?|&)no-gzip\=(1|on|yes|true)'."\n".
						"\t".'RewriteRule .* - [E=no-gzip:1]'."\n".
						'</IfModule>'."\n".
						$end_line;

					if($this->remove_gzip_rules_from_root_htaccess()) // We MUST be able to remove these first (preventing duplicate entries).
						{
							if(is_file($htaccess) && is_readable($htaccess) && is_writable($htaccess) && is_string($htaccess_contents = file_get_contents($htaccess)))
								{
									if(stripos($htaccess_contents, '# BEGIN WordPress') !== FALSE)
										$htaccess_contents = $this->©string->ireplace_once('# BEGIN WordPress', $rules."\n\n".'# BEGIN WordPress', $htaccess_contents);
									else $htaccess_contents = $rules."\n\n".$htaccess_contents;

									$file_put_contents = file_put_contents($htaccess, trim($htaccess_contents));
								}
							else if(!is_file($htaccess) && is_writable($this->©dir->n_seps_up($htaccess)))
								$file_put_contents = file_put_contents($htaccess, $rules);
						}
					if($return_rules) // Returning rules?
						return $rules; // Useful in some scenarios.

					return (!empty($file_put_contents)) ? TRUE : FALSE;
				}

			/**
			 * Removes GZIP rules from root `.htaccess` file.
			 *
			 * @return boolean TRUE if rules were removed (or they did not even exist); else FALSE by default.
			 */
			public function remove_gzip_rules_from_root_htaccess()
				{
					$htaccess   = $this->©dir->n_seps(ABSPATH).'/.htaccess';
					$start_line = '# BEGIN '.$this->___instance_config->plugin_name.' GZIP exclusions';
					$end_line   = '# END '.$this->___instance_config->plugin_name.' GZIP exclusions';

					if(is_file($htaccess) && is_readable($htaccess) && is_writable($htaccess) && is_string($htaccess_contents = file_get_contents($htaccess)))
						{
							$htaccess_contents = trim(preg_replace('/'.preg_quote($start_line, '/').'['."\r\n".']+(?s:.+)['."\r\n".']+'.preg_quote($end_line, '/').'['."\r\n".']{0,2}/i', '', $htaccess_contents));

							return (file_put_contents($htaccess, $htaccess_contents) !== FALSE);
						}
					else if(!is_file($htaccess))
						return TRUE;

					return FALSE; // Default return value.
				}

			/**
			 * Adds data/procedures associated with this class.
			 *
			 * @param boolean $confirmation Defaults to FALSE. Set this to TRUE as a confirmation.
			 *    If this is FALSE, nothing will happen; and this method returns FALSE.
			 *
			 * @return boolean TRUE if successfully installed, else FALSE.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 */
			public function activation_install($confirmation = FALSE)
				{
					$this->check_arg_types('boolean', func_get_args());

					if($confirmation) // Do we have confirmation?
						{
							$this->add_media_rules_into_private_htaccess();
							$this->add_gzip_rules_into_root_htaccess();

							return TRUE;
						}
					return FALSE; // Default return value.
				}

			/**
			 * Removes data/procedures associated with this class.
			 *
			 * @param boolean $confirmation Defaults to FALSE. Set this to TRUE as a confirmation.
			 *    If this is FALSE, nothing will happen; and this method returns FALSE.
			 *
			 * @return boolean TRUE if successfully uninstalled, else FALSE.
			 *
			 * @throws exception If invalid types are passed through arguments list.
			 */
			public function deactivation_uninstall($confirmation = FALSE)
				{
					$this->check_arg_types('boolean', func_get_args());

					if($confirmation) // Do we have confirmation?
						{
							$this->remove_gzip_rules_from_root_htaccess();

							return TRUE;
						}
					return FALSE; // Default return value.
				}
		}
	}