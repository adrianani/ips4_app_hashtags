<?php
/**
 * @brief		trendinghashtags Widget
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @subpackage	hashtags
 * @since		13 Jun 2020
 */

namespace IPS\hashtags\widgets;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * trendinghashtags Widget
 */
class _trendinghashtags extends \IPS\Widget\StaticCache
{
	/**
	 * @brief	Widget Key
	 */
	public $key = 'trendinghashtags';
	
	/**
	 * @brief	App
	 */
	public $app = 'hashtags';
		
	/**
	 * @brief	Plugin
	 */
	public $plugin = '';
	
	/**
	 * Initialise this widget
	 *
	 * @return void
	 */ 
	public function init()
	{
		// Use this to perform any set up and to assign a template that is not in the following format:
		$this->template( array( \IPS\Theme::i()->getTemplate( 'widgets', $this->app, 'front' ), $this->key ) );
		// If you are creating a plugin, uncomment this line:
		// $this->template( array( \IPS\Theme::i()->getTemplate( 'plugins', 'core', 'global' ), $this->key ) );
		// And then create your template at located at plugins/<your plugin>/dev/html/trendinghashtags.phtml
		
		
		parent::init();
	}
	
	/**
	 * Specify widget configuration
	 *
	 * @param	null|\IPS\Helpers\Form	$form	Form object
	 * @return	null|\IPS\Helpers\Form
	 */
	public function configuration( &$form=null )
	{
		$form = parent::configuration( $form );
		
		$form->add( new \IPS\Helpers\Form\Number( 'number_to_show', isset( $this->configuration['number_to_show'] ) ? $this->configuration['number_to_show'] : 5, TRUE, array( 'max' => 25 ) ) );
		$form->add( new \IPS\Helpers\Form\Text( 'hashtags_widget_title', isset( $this->configuration['hashtags_widget_title'] ) ? $this->configuration['hashtags_widget_title'] : \IPS\Member::loggedIn()->language()->addtoStack('hashtags_trending') ) );
		$form->add( new \IPS\Helpers\Form\Text( 'hashtags_trending_filter', isset( $this->configuration['hashtags_trending_filter'] ) ? $this->configuration['hashtags_trending_filter'] : NULL, FALSE, [
			'autocomplete' => [
				'minimized' => FALSE,
				'forceLower' => TRUE,
			]
		] ) );
		$form->add( new \IPS\Helpers\Form\Number( 'hashtags_trending_criteria_count', isset( $this->configuration['hashtags_trending_criteria_count'] ) ? $this->configuration['hashtags_trending_criteria_count'] : -1, FALSE, [
			'unlimited' => -1,
			'unlimitedLang' => 'hashtags_trending_criteria_count_unlimited',
			'min' => 0,
		] ) );
		$form->add( new \IPS\Helpers\Form\Number( 'hashtags_trending_criteria_hours', isset( $this->configuration['hashtags_trending_criteria_hours'] ) ? $this->configuration['hashtags_trending_criteria_hours'] : 8 , FALSE, [
			'min' => 1,
		] ) );

 		return $form;
 	} 
 	
 	 /**
 	 * Ran before saving widget configuration
 	 *
 	 * @param	array	$values	Values from form
 	 * @return	array
 	 */
 	public function preConfig( $values )
 	{
 		return $values;
 	}

	/**
	 * Render a widget
	 *
	 * @return	string
	 */
	public function render()
	{
		$limit = isset( $this->configuration['number_to_show'] ) ? $this->configuration['number_to_show'] : 5;
		$title = isset( $this->configuration['hashtags_widget_title'] ) ? $this->configuration['hashtags_widget_title'] : \IPS\Member::loggedIn()->language()->addtoStack('hashtags_trending');
		$hours = isset( $this->configuration['hashtags_trending_criteria_hours'] ) ? $this->configuration['hashtags_trending_criteria_hours'] : 8;
		$count = isset( $this->configuration['hashtags_trending_criteria_count'] ) ? $this->configuration['hashtags_trending_criteria_count'] : -1;
		$filters = isset( $this->configuration['hashtags_trending_filter'] ) && \is_array( $this->configuration['hashtags_trending_filter'] ) ? $this->configuration['hashtags_trending_filter'] : [];
		$where = [];

		$where[] = [
			'created > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ? DAY_HOUR))',
			$hours
		];

		if( !empty($filters) ) {
			$where[] = \IPS\Db::i()->in( 'hashtag', $filters, TRUE );
		}

		$query = \IPS\Db::i()->select(
			[
				'hashtag',
				'COUNT(*) as `occurences`'
			],
			'hashtags_search_index',
			$where,
			'occurences DESC',
			$limit,
			'hashtag',
			$count < 1 ? NULL : [
				'COUNT(*) >= ?',
				$count
			]
		);

		try {
			$hashtags = iterator_to_array( $query );
		} catch ( \IPS\Db\Exception $e ) {
			$hashtags = [];
		}

		return $this->output( $hashtags, $title );
		
	}
}