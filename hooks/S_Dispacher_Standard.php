//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

abstract class hashtags_hook_S_Dispacher_Standard extends _HOOK_CLASS_
{

	/**
	 * Output the basic javascript files every page needs
	 *
	 * @return void
	 */
	static protected function baseJs()
	{
		parent::baseJs();
      
		if ( !\IPS\Request::i()->isAjax() ) {
			\IPS\Output::i()->jsFiles = array_merge( \IPS\Output::i()->jsFiles, \IPS\Output::i()->js( 'hashtags.search.js', 'hashtags', 'interface' ) );
		}
	}
	
	public static function baseCss()
	{
		try
		{
			parent::baseCss();
			\IPS\Output::i()->cssFiles = array_merge( \IPS\Output::i()->cssFiles, \IPS\Theme::i()->css( 'hashtags.css', 'hashtags', 'front' ) );
		}
		catch ( \RuntimeException $e )
		{
			if ( method_exists( get_parent_class(), __FUNCTION__ ) )
			{
				return \call_user_func_array( 'parent::' . __FUNCTION__, \func_get_args() );
			}
			else
			{
				throw $e;
			}
		}
	}

}
