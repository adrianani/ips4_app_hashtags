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
		// This is the default method if no 'do' parameter is specified
	}
	
	// Create new methods with the same name as the 'do' parameter which should execute it
}