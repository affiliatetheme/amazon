<?php
/**
 * Project      affiliatetheme-amazon
 * @author      Giacomo Barbalinardo <info@ready24it.eu>
 * @copyright   2019
 *
 * Rewritten 2026: Consumes Amazon Creators API SearchItems responses
 * (associative array, JSON-decoded).
 */

namespace Endcore;

class FormattedResponse {

	/**
	 * Full Creators-API response (associative array).
	 *
	 * @var array
	 */
	private $response;

	/**
	 * The "searchResult" sub-array, or null if not present.
	 *
	 * @var array|null
	 */
	private $result;

	/**
	 * @param array $response
	 */
	public function __construct( $response ) {
		$this->response = is_array( $response ) ? $response : array();
		$this->result   = isset( $this->response['searchResult'] ) && is_array( $this->response['searchResult'] )
			? $this->response['searchResult']
			: null;
	}

	/**
	 * @param int $itemsPerPage
	 * @return float|int
	 */
	public function getTotalPages( $itemsPerPage = 10 ) {
		if ( ! $this->hasResult() ) {
			return 0;
		}

		$total = isset( $this->result['totalResultCount'] ) ? (int) $this->result['totalResultCount'] : 0;

		if ( $itemsPerPage <= 0 ) {
			return 0;
		}

		return ceil( $total / $itemsPerPage );
	}

	/**
	 * @return SimpleItem[]|bool
	 */
	public function getItems() {
		if ( ! $this->hasResult() ) {
			return false;
		}

		$items = isset( $this->result['items'] ) && is_array( $this->result['items'] )
			? $this->result['items']
			: array();

		return array_map( function ( $item ) {
			return new SimpleItem( $item );
		}, $items );
	}

	/**
	 * @return string
	 */
	public function getErrorMessage() {
		if ( isset( $this->response['errors'] ) && is_array( $this->response['errors'] ) && count( $this->response['errors'] ) > 0 ) {
			$first = $this->response['errors'][0];
			if ( is_array( $first ) && isset( $first['message'] ) ) {
				return $first['message'];
			}
		}

		return '';
	}

	/**
	 * @return bool
	 */
	public function hasResult() {
		return $this->result !== null;
	}
}
