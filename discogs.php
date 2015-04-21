<?php
header('Content-Type: text/html; charset=utf-8');

define('_ENGINE', true);

class DiscogsAPI {

	private static  $user_agent       = 'theglobalmusiccommunity.com/0.1 +http://www.theglobalmusiccommunity.com',
									$consumerKey      = 'STRKKQdhGxbjEWibGIFK',
									$consumerSecret   = 'vLihrzEolIUbexlKoOmpiScKuudZgAlt',
									$scope            = 'https://api.discogs.com',
									$pagination_start = '<div class="discogs-pagination">',
									$pagination_end   = '</div>',
									$link_template    = '<div class="{{label}}"><a class="discogs-page-link" href="{{url}}?artist={{artist}}&page={{page}}" data-artist="{{artist}}" data-page="{{page}}">{{label}}</a></div>',
									$release_template = '
<div class="discogs_release">
	<img class="thumbnail" src="{{thumb}}" />
	<div class="title">{{title}} - {{artist}} ({{year}})</div>
	<div class="label">{{label}}</div>
	<div class="role">{{role}}</div>
	<div class="discogs_url"><a href="http://www.discogs.com/{{url}}/release/{{id}}" target="_blank">View on discogs.com</a></div>
</div>
';

	private $endpoint,
					$atist_endpoint   = '/artists/{{artist}}/releases',
					$artist,
					$page,
					$page_size = 10,
					$pagination,
					$releases,
					$output;



	public function __construct(){

		$this->releases = new stdClass();

		// Instiate variables
		$this->artist = $_GET['artist'];
		$this->page = $_GET['page'];

		$this->idiot_check();

		$this->endpoint = str_replace('{{artist}}', $this->artist, $this->atist_endpoint);

		$this->releases->json = $this->curl_one_out();

		$this->releases->object = json_decode($this->releases->json);

		$this->borked_results_check();

		$this->boring_pagination_stuff();

		foreach($this->releases->object->releases as $release){
			$release->output = self::$release_template;
			$release->url = preg_replace('/\s/', '-', $release->artist . '-' . $release->title);
			$release->url = preg_replace('/[^a-zA-Z0-9-]/', '', $release->url);

			foreach($release as $key => $value){
				$value = $this->degibberishify($value);
				$value = htmlentities($value);
				$release->output = str_replace('{{' . $key . '}}', $value, $release->output);
				// $all_data .= '<div class="all-data">' . $key . ' : ' .  $value . '</div>';
			}

			$release->output = preg_replace('/\{\{[^\}]+\}\}/', '', $release->output);
			// $this->output .= $release->output . $all_data;
			$this->output .= $release->output;
		}
	}



	public function display_pagination(){
		echo $this->pagination;
	}



	public function display_releases(){
		echo $this->output;
	}



	public function display_releases_object(){
		echo $this->preformat($this->releases->object);
	}



	public function display_json(){
		echo $this->preformat($this->releases->json);
	}



	private function idiot_check(){

		if(!is_numeric($this->artist)){
			$this->throw_up('Artist ID must be numeric');
		}

		if(!is_numeric($this->page) || $this->page < 1){
			$this->throw_up('Page number must be numeric and a positive value');
		}

	}



	private function boring_pagination_stuff(){

		$total_pages = $this->releases->object->pagination->pages;
		$pagination = '';

		foreach($this->releases->object->pagination->urls as $label => $url){

			if($label === 'first'){
				$pagination .= $this->build_page_link($label, 1);
			}

			if($label === 'prev'){
				$pagination .= $this->build_page_link($label, $this->page - 1);
			}

			if($label === 'next'){
				$pagination .= $this->build_page_link($label, $this->page + 1);
			}

			if($label === 'last'){
				$pagination .= $this->build_page_link($label, $total_pages);
			}
		}

		$this->pagination = self::$pagination_start . $pagination . self::$pagination_end;

	}



	private function build_page_link($label, $page){

		$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
		$url      = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

		$link = str_replace('{{url}}', $url, self::$link_template);
		$link = str_replace('{{artist}}', $this->artist, $link);
		$link = str_replace('{{page}}', $page, $link);
		$link = str_replace('{{label}}', $label, $link);

		return $link;

	}



	private function curl_one_out(){

		$ch         = curl_init();
		$page_query = ($this->page > 0) ? '?page=' . $this->page  . '&per_page=' . $this->page_size . '&key=' . self::$consumerKey . '&secret=' . self::$consumerSecret : '';
		$result;

		$headers = array(
			"POST " . $this->endpoint . " HTTP/1.0",
			"Content-type: text/xml;charset=\"utf-8\"",
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Pragma: no-cache",
			'Authorization: Discogs key=' . self::$consumerKey . ', secret=' . self::$consumerSecret
		);

		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, self::$user_agent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_URL, self::$scope . $this->endpoint . $page_query);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);

		$response                        = curl_exec($ch);
		$header_size                     = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$this->releases->response_header = substr($response, 0, $header_size);
		$result                          = substr($response, $header_size);

		curl_close($ch);

		return $result;

	}



	private function borked_results_check(){
		if (isset($this->releases->object->message)){
			$this->throw_up($this->releases->object->message);
		}
	}



	public function degibberishify($string){
		return utf8_decode( $string );
	}



	private function throw_up($message){

		header('HTTP/1.1 500 Internal Server Error', true, 500);
		echo $message;
		trigger_error($message, E_USER_ERROR);

	}

	private function preformat($object){
		return '<pre>' + print_r($object) . '</pre>';
	}

}

$prefile_releases = new DiscogsAPI();

$prefile_releases->display_releases();
$prefile_releases->display_pagination();
?>