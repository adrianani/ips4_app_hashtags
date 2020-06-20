//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class hashtags_hook_A_Core_Front_Search_search extends _HOOK_CLASS_
{

	protected function manage() {

		if( !isset(\IPS\Request::i()->q) && !isset(\IPS\Request::i()->type) !== 'core_members' && !isset(\IPS\Request::i()->tags) && isset(\IPS\Request::i()->hashtags) ) {
			
			\IPS\Output::i()->sidebar['enabled'] = FALSE;
			\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'styles/streams.css' ) );
			\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'styles/search.css' ) );
			if ( \IPS\Theme::i()->settings['responsive'] )
			{
				\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'styles/search_responsive.css' ) );
			}

			\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'front_search.js', 'core' ) );	
			\IPS\Output::i()->metaTags['robots'] = 'noindex';

			if( !\IPS\Settings::i()->tags_enabled and isset( \IPS\Request::i()->tags ) )
			{
				\IPS\Output::i()->error( 'page_doesnt_exist', 'HT000', 404, '' );
			}

			return $this->_results();
			
		}

		parent::manage();
	}

}
