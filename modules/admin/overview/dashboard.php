<?php

namespace IPS\hashtags\modules\admin\overview;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * dashboard
 */
class _dashboard extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'dashboard_manage' );
		parent::execute();
	}

	/**
	 * SELECT hashtag, COUNT(*) AS occurences FROM hashtags_hashtags WHERE created >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 24 DAY_HOUR)) GROUP BY hashtag ORDER BY occurences DESC LIMIT 10;
	 * 
	 * @return	void
	 */
	protected function manage()
	{
		
		\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'hashtags.css' ) );
		
		$allTimeMostUsed = \IPS\Db::i()->query("SELECT hashtag, COUNT(*) AS occurences FROM hashtags_hashtags GROUP BY hashtag ORDER BY occurences DESC LIMIT 1")->fetch_assoc();
		$todayMostUsed = \IPS\Db::i()->query("SELECT hashtag, COUNT(*) AS occurences FROM hashtags_hashtags WHERE DATE_FORMAT(FROM_UNIXTIME(created), '%Y-%m-%d') = CURDATE() GROUP BY hashtag ORDER BY occurences DESC LIMIT 1")->fetch_assoc();
		$quickStats = [
			'uniq_hashtags' => \IPS\Db::i()->query("SELECT COUNT(*) as uniq FROM (SELECT DISTINCT created FROM hashtags_hashtags) unique_hashtags")->fetch_assoc()['uniq'],
			'ever_hashtag' => !empty($allTimeMostUsed['hashtag']) ? $allTimeMostUsed['hashtag'] : 0,
			'ever_hashtag_use' => !empty($allTimeMostUsed['occurences']) ? $allTimeMostUsed['occurences'] : 0,
			'today_hashtags' => \IPS\Db::i()->query("SELECT COUNT(*) AS today FROM (SELECT DISTINCT created AS created_date FROM hashtags_hashtags WHERE DATE_FORMAT(FROM_UNIXTIME(created), '%Y-%m-%d') = CURDATE()) today_hashtags")->fetch_assoc()['today'],
			'today_hashtag' => !empty($todayMostUsed['hashtag']) ? $todayMostUsed['hashtag'] : 0,
			'today_hashtag_use' => !empty($todayMostUsed['occurences']) ? $todayMostUsed['occurences'] : 0,
		];

		$chart = new \IPS\Helpers\Chart\Database(
			\IPS\Http\Url::internal('app=hashtags&module=overview&controller=dashboard&do=chart'),
			'hashtags_hashtags',
			'created',
			'',
			[
				'isStacked' => TRUE,
				'backgroundColor' 	=> '#ffffff',
				'colors'			=> array( '#10967e', '#ea7963', '#de6470', '#6b9dde', '#b09be4', '#eec766', '#9fc973', '#e291bf', '#55c1a6', '#5fb9da' ),
				'hAxis'				=> array( 'gridlines' => array( 'color' => '#f5f5f5' ) ),
				'lineWidth'			=> 1,
				'areaOpacity'		=> 0.4
			],
			'AreaChart',
			'monthly'
		);

		$chart->groupBy = 'meta_app';

		foreach( \IPS\Db::i()->query('SELECT DISTINCT meta_app FROM hashtags_hashtags') as $row ) {
			$chart->addSeries( 
				\IPS\Member::loggedIn()->language()->addToStack('hashtags_stats_used_app_hashtags') . \IPS\Member::loggedIn()->language()->addToStack('__app_' . $row['meta_app']),
				'number',
				'COUNT(*)',
				TRUE,
				$row['meta_app']
			);
		}

		$chart->availableTypes = [ 'AreaChart', 'ColumnChart', 'BarChart' ];

		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('menu__hashtags_overview_dashboard');
		\IPS\Output::i()->output .= \IPS\Theme::i()->getTemplate( 'overview' )->dashboard($quickStats, $chart);
	}

	public function chart() {
		$chart = new \IPS\Helpers\Chart\Database(
			\IPS\Http\Url::internal('app=hashtags&module=overview&controller=dashboard&do=chart'),
			'hashtags_hashtags',
			'created',
			'',
			[
				'isStacked' => TRUE,
				'backgroundColor' 	=> '#ffffff',
				'colors'			=> array( '#10967e', '#ea7963', '#de6470', '#6b9dde', '#b09be4', '#eec766', '#9fc973', '#e291bf', '#55c1a6', '#5fb9da' ),
				'hAxis'				=> array( 'gridlines' => array( 'color' => '#f5f5f5' ) ),
				'lineWidth'			=> 1,
				'areaOpacity'		=> 0.4
			],
			'AreaChart',
			'monthly'
		);

		$chart->groupBy = 'meta_app';

		foreach( \IPS\Db::i()->query('SELECT DISTINCT meta_app FROM hashtags_hashtags') as $row ) {
			$chart->addSeries( 
				\IPS\Member::loggedIn()->language()->addToStack('hashtags_stats_used_app_hashtags') . \IPS\Member::loggedIn()->language()->addToStack('__app_' . $row['meta_app']),
				'number',
				'COUNT(*)',
				TRUE,
				$row['meta_app']
			);
		}

		$chart->availableTypes = [ 'AreaChart', 'ColumnChart', 'BarChart' ];

		\IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('hashtags_usage_stats');
		\IPS\Output::i()->output .= $chart;
	}
	
	// Create new methods with the same name as the 'do' parameter which should execute it
}