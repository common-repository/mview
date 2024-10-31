<?php

/**
 * Name: mView
 * Description: mView plugin build for wordpress
 * Version: 1.0.1
 * Author: mufeng
 * Url: http://mufeng.me
 */
class MView
{
	public function __construct ()
	{
		add_shortcode ( 'mView' , array ( $this , 'shortcode' ) );
		add_action ( 'wp_enqueue_scripts' , array ( $this , 'scripts' ) );
		add_action ( 'wp_ajax_nopriv_mview' , array ( $this , 'api' ) );
		add_action ( 'wp_ajax_mview' , array ( $this , 'api' ) );
		add_action ( 'wp_ajax_mview_fetch' , array ( $this , 'fetch' ) );
		add_action ( 'wp_ajax_mview_upload' , array ( $this , 'upload' ) );
		add_action ( 'admin_menu' , array ( $this , 'menu' ) );
		// add_action ( 'media_buttons_context' , array ( $this , 'custom_button' ) );

		if ( !wp_next_scheduled ( 'mview_hourly_event' ) ) {
			wp_schedule_event ( time () , 'hourly' , 'mview_hourly_event' );
		}

		add_action ( 'mview_hourly_event' , array ( $this , 'auto_sync' ) );
	}

	public function scripts ()
	{
		wp_enqueue_style ( 'mview' , MVIEW_URL . '/assets/css/mview-f3562129b6.css' );

		if ( is_page ( $this->settings ( 'pageId' ) ) ) {
			wp_enqueue_script ( 'mview' , MVIEW_URL . '/assets/js/mview-59da743ddc.js' , false , false , $this->settings ( 'jsplace' ) );
			wp_localize_script ( 'mview' , 'mView' , array (
				'api' => MVIEW_ADMIN_URL . 'admin-ajax.php' ,
				'version' => MVIEW_VERSION ,
				'loaderTips' => $this->settings ( 'loaderTips' ) ,
				'endTips' => $this->settings ( 'endTips' ) ,
				'page' => 1 ,
				'maxPage' => ceil ( $this->count () / 30 )
			) );
		}
	}

	public function shortcode ( $atts )
	{
		$params = shortcode_atts ( array (
			'id' => null
		) , $atts );

		if ( $params['id'] ) {
			$data = $this->get_collection ( $params['id'] );
			return $this->render_html ( $data , false , 'collection' );
		} else {
			$data = $this->get_by_page ( 1 );
			return $this->render_html ( $data );
		}
	}

	public function api ()
	{
		$page = intval ( $_GET['page'] );
		$data = $this->get_by_page ( $page );

		$this->json ( $data );
	}

	public function menu ()
	{
		add_options_page ( 'mView' , 'mView' , 'manage_options' , basename ( __FILE__ ) , array ( $this , 'setting_page' ) );
		add_action ( 'admin_init' , array ( $this , 'setting_group' ) );
	}

	public function setting_group ()
	{
		register_setting ( 'mview_setting_group' , 'mview_settings' );
	}

	public function setting_page ()
	{
		@include 'setting.php';
	}

	public function custom_button ( $context )
	{
		$context .= "<a id='mView-show' class='button' href='javascript:;' title='mView'>mView</a>";
		return $context;
	}

	public function render_html ( $data , $lazyload = true , $container_class = 'container' )
	{
		?>
		<div class="mview--<?php echo $container_class; ?>">
			<ul class="mview--ul mview--clear">
				<?php
				foreach ( $data as $key => $row ) {
					?>
					<li class="mview--li">
						<a class="mview--link" href="https://m.douban.com/movie/subject/<?php echo $row->movie_id; ?>"
						   target="_blank">
							<div class="mview--cover">
								<img class="mview--cover--image" <?php if ( $lazyload ) { echo 'data-original';} else { echo 'src';} ?>="<?php echo $row->movie_cover; ?>" alt="<?php echo $row->movie_title; ?>" width="150" height="220"/>
							</div>
							<div class="mview--info">
								<div class="mview--title"><?php echo $row->movie_title; ?></div>
								<div class="mview--rank">
					                <span class="mview--rank--span mview--stars">
						                  <?php
						                  $real_rank = round ( $row->movie_rating / 2 );

						                  for ( $index = 1; $index <= 5; $index++ ) {
							                  if ( $index <= $real_rank ) {
								                  echo '<span class="mview--star mview--star--full"></span>';
							                  } else {
								                  echo '<span class="mview--star mview--star--gray"></span>';
							                  }
						                  }
						                  ?>
					                </span>
									<span class="mview--rank--span"><?php echo $row->movie_rating; ?></span>
								</div>
							</div>
						</a>
					</li>
				<?php }
				?>
			</ul>
			<div class="mview--loader">
				<span class="mview--loader--spinner">
					<i class="mview--loader--spinner--icon"></i>
				</span>
				<span class="mview--loader--text"></span>
			</div>
		</div>
		<?php
	}

	public function get_by_page ( $page )
	{
		global $wpdb , $mview_table_name;

		$limit = 30;
		$offset = ( $page - 1 ) * $limit;

		$query = "SELECT id,movie_title,movie_id,movie_cover,movie_rating,created FROM {$mview_table_name} ORDER BY `created` DESC LIMIT {$limit} OFFSET {$offset}";
		$results = $wpdb->get_results ( $query );
		$results = $this->parser ( $results );

		return $results;
	}

	public function get_collection ( $params_id )
	{
		global $wpdb , $mview_table_name;

		$query = "SELECT id,movie_title,movie_id,movie_cover,movie_rating,created FROM {$mview_table_name} WHERE id IN ({$params_id}) order by field(id, {$params_id})";
		$results = $wpdb->get_results ( $query );
		$results = $this->parser ( $results );

		return $results;
	}

	public function parser ( $data )
	{
		foreach ( $data as $key => $row ) {
			$url = MVIEW_URL . '/uploads/' . $row->movie_cover;
			$cdnServer = $this->settings ( 'cdnServer' );
			$customUrl = $this->settings ( 'customUrl' );

			if ( $cdnServer == 'qiniu' ) {
				$url .= '?imageView/1/w/150/h/220/q/100';
			} else if ( $cdnServer == 'youpai' ) {
				$url .= '_/both/150x220';
			}

			if ( isset( $customUrl ) && ( $cdnServer == 'qiniu' || $cdnServer == 'youpai' ) ) {
				$customUrl = preg_replace ( '/\/$/' , '' , $customUrl );
				$url = str_replace ( MVIEW_SITE_URL , $customUrl , $url );
			}

			$data[$key]->movie_cover = $url;
		}

		return $data;
	}

	public function count ()
	{
		global $wpdb , $mview_table_name;

		$query = "SELECT COUNT(id) AS count FROM {$mview_table_name}";
		$count = $wpdb->get_var ( $query );

		return $count;
	}

	public function settings ( $key )
	{
		$defaults = array (
			'loaderTips' => '啦啦啦加载中...' ,
			'endTips' => 'ʅ(‾◡◝)就这些电影啦' ,
			'cdnServer' => null ,
			'customUrl' => null ,
			'doubanId' => null ,
			'pageId' => null ,
			'jsplace' => 0
		);

		$settings = get_option ( 'mview_settings' );
		$settings = wp_parse_args ( $settings , $defaults );

		return $settings[$key];
	}

	public function fetch ( $forEvent = false )
	{
		$id = $forEvent ? $this->settings ( 'doubanId' ) : $_POST['id'];
		$page = $forEvent ? 1 : ( $_POST['page'] ? intval ( $_POST['page'] ) : 1 );
		$limit = $forEvent ? 10 : 50;
		$offset = ( $page - 1 ) * $limit;

		$url = "http://api.douban.com/people/{$id}/collection?alt=json&&apikey=0df993c66c0c636e29ecbb5344252a4a&app_name=doubanmovie&cat=movie&max-results=50&start-index={$offset}&status=watched";
		$header = array (
			'Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8' ,
			'Accept-Encoding:gzip, deflate, sdch' ,
			'Accept-Language:en-US,en;q=0.8,zh-CN;q=0.6,zh;q=0.4' ,
			'Cache-Control:max-age=0' ,
			'Connection:keep-alive' ,
			'DNT:1' ,
			'Host:api.douban.com' ,
			'Upgrade-Insecure-Requests:1' ,
			'User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.116 Safari/537.36'
		);

		$ch = curl_init ( $url );
		curl_setopt ( $ch , CURLOPT_HTTPHEADER , $header );
		curl_setopt ( $ch , CURLOPT_ENCODING , 'gzip' );
		curl_setopt ( $ch , CURLOPT_RETURNTRANSFER , true );
		$cexecute = curl_exec ( $ch );
		@curl_close ( $ch );

		if ( $cexecute ) {
			$results = json_decode ( $cexecute , true );
			$count = $results['opensearch:totalResults']['$t'];
			$maxPage = ceil ( $count / $limit );
			$entry = $results['entry'];
			$response = array (
				'page' => $page ,
				'maxPage' => $maxPage ,
				'count' => $count ,
				'items' => array ()
			);

			foreach ( $entry as $key => $val ) {
				$movie = $val['db:subject'];

				$attribute = $movie['db:attribute'];
				$title = null;

				foreach ( $attribute as $k => $v ) {
					if ( array_key_exists ( '@lang' , $v ) && $v['@lang'] == 'zh_CN' ) {
						$title = $v['$t'];
					}
				}

				preg_match ( '/.*?subject\/(\d+)/i' , $movie['id']['$t'] , $matches );

				$response['items'][] = array (
					'movie_title' => $title ? $title : $movie['title']['$t'] ,
					'movie_id' => $matches[1] ,
					'movie_cover' => $movie['link'][4]['@href'] ,
					'movie_rating' => $movie['gd:rating']['@average'] ,
					'created' => date_format ( date_create ( $val['updated']['$t'] ) , 'Y-m-d H:i:s' )
				);
			}

			if ( $forEvent ) {
				return $response['items'];
			} else {
				$this->json ( $response );
			}

		} else {
			if ( $forEvent ) {
				return false;
			} else {
				$this->json ( array (
					'error' => 'Fetch error'
				) );
			}
		}
	}

	public function upload ( $forEvent = false )
	{
		global $wpdb , $mview_table_name;

		$movie_title = $forEvent ? $forEvent['movie_title'] : $_POST['movie_title'];
		$movie_id = $forEvent ? $forEvent['movie_id'] : $_POST['movie_id'];
		$movie_rating = $forEvent ? $forEvent['movie_rating'] : $_POST['movie_rating'];
		$created = $forEvent ? $forEvent['created'] : $_POST['created'];

		$url = $forEvent ? $forEvent['movie_cover'] : $_POST['movie_cover'];
		$urlInfo = pathinfo ( $url );
		$fileName = $urlInfo['basename'];

		if ( $this->is_existed ( $movie_id ) ) {
			$this->download ( $url );
			return $this->json ( array (
				'error' => 'MVIEWEXISTED'
			) );
		}

		$download_result = $this->download ( $url );

		$newData = array (
			'movie_id' => $movie_id ,
			'movie_title' => $movie_title ,
			'movie_cover' => $fileName ,
			'movie_rating' => $movie_rating ,
			'movie_status' => 'watched' ,
			'created' => $created
		);

		$wpdb->insert ( $mview_table_name , $newData , array ( '%s' , '%s' , '%s' , '%s' , '%s' , '%s' ) );
		$newData['download_result'] = $download_result;

		if ( $forEvent ) {
			return $newData;
		} else {
			$this->json ( $newData );
		}
	}

	public function is_existed ( $movie_id )
	{
		global $wpdb , $mview_table_name;

		$mdata = $wpdb->get_var ( "SELECT id,movie_id FROM {$mview_table_name} WHERE `movie_id` = '{$movie_id}' LIMIT 1" );

		if ( isset( $mdata ) ) {
			return true;
		}

		return false;
	}

	public function filter ( $movie_id_array )
	{
		global $wpdb , $mview_table_name;

		$new_id_array = array ();
		$movie_id_string = implode ( ',' , $movie_id_array );
		$query = "SELECT movie_id FROM {$mview_table_name} WHERE `movie_id` IN ({$movie_id_string})";
		$results = $wpdb->get_results ( $query );

		foreach ( $results as $key => $val ) {
			$new_id_array[] = $val->movie_id;
		}

		return array_merge ( array_diff ( $movie_id_array , $new_id_array ) );
	}

	public function auto_sync ()
	{
		$data = $this->fetch ( true );

		if ( $data ) {
			$movie_id_array = array ();

			foreach ( $data as $key => $val ) {
				$movie_id_array[] = $val['movie_id'];
			}

			if ( count ( $movie_id_array ) < 1 ) {
				return;
			}

			$filter_id_array = $this->filter ( $movie_id_array );

			if ( count ( $filter_id_array ) < 1 ) {
				return;
			}

			foreach ( $data as $key => $val ) {
				if ( in_array ( $val['movie_id'] , $filter_id_array ) ) {
					$this->upload ( $val );
				}
			}
		}
	}

	public function download ( $url )
	{
		$urlInfo = pathinfo ( $url );
		$fileName = $urlInfo['basename'];
		$filePath = MVIEW_PATH . '/uploads/' . $fileName;

		if ( !file_exists ( $filePath ) ) {
			ob_start ();
			@readfile ( $url );
			$img_data = ob_get_contents ();
			ob_end_clean ();
			$local_file = @fopen ( $filePath , 'a' );
			@fwrite ( $local_file , $img_data );
			@fclose ( $local_file );
		}

		return $fileName;
	}

	public function json ( $data )
	{
		$data = json_encode ( $data );
		header ( 'Content-type: application/json;charset=UTF-8' );
		exit( $data );
	}
}
