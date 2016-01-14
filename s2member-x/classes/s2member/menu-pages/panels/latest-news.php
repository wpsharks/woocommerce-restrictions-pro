<?php
/**
 * Menu Page Panel.
 *
 * Copyright: © 2012 (coded in the USA)
 * {@link http://www.websharks-inc.com WebSharks™}
 *
 * @author JasWSInc
 * @package s2Member\Menu_Pages\Panels
 * @since 120318
 */
namespace s2member\menu_pages\panels
	{
		if(!defined('WPINC'))
			exit('Do NOT access this file directly: '.basename(__FILE__));

		/**
		 * Menu Page Panel.
		 *
		 * @package s2Member\Menu_Pages\Panels
		 * @since 120318
		 *
		 * @assert ($GLOBALS[__NAMESPACE__])
		 */
		class latest_news extends panel
		{
			/**
			 * Constructor.
			 *
			 * @param object|array                   $___instance_config Required at all times.
			 *    A parent object instance, which contains the parent's ``$___instance_config``,
			 *    or a new ``$___instance_config`` array.
			 *
			 * @param \s2member\menu_pages\menu_page $menu_page A menu page class instance.
			 *
			 * @throws \s2member\exception If invalid types are passed through arguments list.
			 */
			public function __construct($___instance_config, $menu_page)
				{
					parent::__construct($___instance_config, $menu_page);

					$this->heading_title = $this->i18n('Latest News / KB Articles');

					$this->content_body =

						'<div>'.
						'<img src="'.esc_attr($this->©url->to_plugin_dir_file('/client-side/images/kb-article-60x60.png')).'" style="width:60px; height:60px; background:#FFFFFF; border:2px solid #EEEEEE; border-radius:5px; float:right; margin:0 0 0 10px;" alt="" />'.
						sprintf($this->i18n(
							        '<p style="margin-top:0;">These are the latest news &amp; knowledge base articles from s2Member.com.</p>'.
							        '<p class="clear" style="margin-bottom:0;"><strong>See also:</strong> <a href="%1$s" target="_blank" rel="xlink">s2Member® FAQs</a></p>'
						        ), esc_attr($this->©url->to_plugin_site_uri('/faqs/'))).
						'</div>'.

						'<hr />'.

						'<div class="feed">';

					// Get feed items.
					foreach($this->©feed->items('http://feeds.feedburner.com/s2member-updates', 3) as $_item)
						$this->content_body .=
							'<div class="feed-item">'.

							'<div class="feed-item-title">'.
							'<a href="'.esc_attr($_item['link']).'" title="'.esc_attr($_item['title']).'" target="_blank" rel="xlink">'.
							$this->©string->excerpt($_item['title'], 35).
							'</a>'.
							'</div>'.

							'<div class="feed-item-excerpt">'.
							$this->©string->excerpt($_item['excerpt'], 185).
							'</div>'.

							'<div class="feed-item-date">'.
							$this->©date->i18n_('M jS, Y', $_item['time']).
							'</div>'.

							'</div>';
					unset($_item);

					$this->content_body .=
						'</div>'.

						'<hr />'.

						'<div style="text-align:center;">'.
						sprintf($this->i18n(
							        '<p style="margin:0;"><a href="%1$s" target="_blank" rel="xlink">s2Member® Blog &amp; Knowledge Base</a></p>'
						        ), esc_attr($this->©url->to_plugin_site_uri('/kb/'))).
						'</div>';
				}
		}
	}