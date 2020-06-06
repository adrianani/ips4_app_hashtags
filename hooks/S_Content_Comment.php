//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

abstract class hashtags_hook_S_Content_Comment extends _HOOK_CLASS_
{

	public static function create( $item, $comment, $first=FALSE, $guestName=NULL, $incrementPostCount=NULL, $member=NULL, \IPS\DateTime $time=NULL, $ipAddress=NULL, $hiddenStatus=NULL ) {

		if ( $member === NULL )
		{
			$member = \IPS\Member::loggedIn();
		}

		$comment = preg_replace_callback(
			'/(^|\s)(#(([a-zA-Z]([\w]+)|([0-9]+)[a-zA-Z]+)))/iu',
			function( $matches ) use ( $item, $member ) {
				$url = \IPS\Http\Url::internal('app=hashtags&module=hashtags&controller=search&hashtag=' . $matches[3]);
				$itemColumnId = $item::$databaseColumnId;

				\IPS\Db::i()->insert(
					'hashtags_hashtags',
					[
						'hashtag' => $matches[3],
						'meta_id' => $item->$itemColumnId,
						'meta_app' => $item::$application,
						'meta_module' => $item::$module,
						'meta_parent_id' => $item->container()->_id,
						'meta_member_id' => $member->member_id,
						'created' => time(),
					]
				);

				return "{$matches[1]}<a a href='{$url}' rel='tag'>{$matches[2]}</a>";
			},
			$comment
		);

		return parent::create($item, $comment, $first, $guestName, $incrementPostCount, $member, $time, $ipAddress, $hiddenStatus);
	}

}
