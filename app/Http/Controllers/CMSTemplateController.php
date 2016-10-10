<?php

namespace App\Http\Controllers;

use App\Http\Controllers\SiteController;
use Auth;
use DB;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use View;

class CMSTemplateController extends Controller {

	private $data = "";

	public function __construct() {
		$page = \Route::current()->getParameter('page');
		$config_path = $this->determineConfigPath($page);
		$this->data = $this->processTemplate($config_path);
	}

	public function index($page) {
		if (Auth::user()->can("view_{$page}")) {
			$shortlist = $this->getShortlist();
			$table = $this->getTable();
			$key = $this->getKey();
			$where = $this->getWhere();
			$order_by = $this->getOrderBy();

			// Select the key at the very minimum
			$items = DB::select("select {$key}, {$shortlist} from {$table} {$where} {$order_by}");

			$this->determineViewPath('index', $page);

			$return_data = [
				'items' => $items,
				'shortlist' => $this->data['shortlist'],
				'page' => $page,
				'meta_info' => $this->data,
			];

			return view('pages.index', $return_data);
		} else {
			return back()->withErrors(['message' => 'You don\'t have permission to do that']);
		}
	}
	public function create($page) {

	}
	public function store(Request $request, $page) {

	}
	public function show($page, $id) {

	}
	public function edit($page, $id) {

	}
	public function update(Request $request, $page, $id) {

	}
	public function destroy($page, $encrypted_id) {
		if (Auth::user()->can('delete_' . $page)) {

			$table = $this->getTable();
			$key = $this->getKey();

			try {
				// delete the item from the database
				$id = decrypt($encrypted_id);

				DB::delete("DELETE FROM {$table} WHERE {$key} = :id", [$id]);
				return back();
			} catch (DecryptException $e) {

				dd($e->getMessage());
				return back()->withErrors(['message' => 'Could not decrypt item key']);
			}
		} else {
			return back()->withErrors(['message' => 'You don\'t have permission to do that']);
		}
	}

	public function getTable() {
		return $this->sanitize($this->data['table']);
	}

	public function getKey() {
		return $this->sanitize($this->data['key']);
	}

	public function getWhere() {
		if (isset($this->data['where'])) {
			return $this->sanitize($this->data['where']);
		} else {
			return "";
		}
	}

	public function getShortlist() {
		if (isset($this->data['shortlist']) && !empty($this->data['shortlist'])) {
			return $this->sanitize(implode(',', $this->data['shortlist']));
		} else {
			return "*";
		}
	}

	public function getOrderBy() {
		if (isset($this->data['order_by']) && !empty($this->data['order_by'])) {
			return "ORDER BY {$this->data['order_by']}";
		} else {
			return "";
		}
	}

	/**
	 * Determines the correct filepath for the config file (allowing developers
	 * to override any default configs) and whether it exists
	 * @param  string $page The name of the config file (and the page)
	 * @return mixed        Returns a string on success, null on failure
	 */
	private function determineConfigPath($page) {
		$site_config_path = SiteController::getSitePath() . '/cms/' . $page . '.json';

		if (file_exists($site_config_path)) {
			return $site_config_path;
		} else if (file_exists($config_path = base_path('cms/' . $page . '.json'))) {
			return $config_path;
		}

		return null;
	}

	private function determineViewPath($view, $page) {
		$site = SiteController::getSite();

		if (View::exists($view_path = $site . ".cms.pages." . $page . '.' . $view)) {
			return $view_path;
		} else if (View::exists($view_path = "pages." . $page . '.' . $view)) {
			return $view_path;
		} else {
			return ("pages.{$view}");
		}
	}

	private function processTemplate($file_location) {
		$file = file_get_contents($file_location);

		return json_decode($file, true);
	}

	private function sanitize($input) {
		if (!preg_match('/(\?|\.|\\|\/)/', $input, $matches)) {
			return $input;
		} else {
			return back()->withErrors(['message' => 'The following characters are illegal: ? . / \ ']);
		}
	}
}
