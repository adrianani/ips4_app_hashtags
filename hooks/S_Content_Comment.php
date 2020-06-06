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

		$obj = parent::create($item, $comment, $first, $guestName, $incrementPostCount, $member, $time, $ipAddress, $hiddenStatus);
		$columnContent = static::$databaseColumnMap['content'];
		$columnId = static::$databaseColumnId;
		$comment = preg_replace_callback(
			'/(^|\s)(<a (([a-zA-Z]+)=(\u0027|\u0022)([^\u0022]*|[^\u0027]*)(\u0027|\u0022)*)>)?(#([a-zA-Z]([\w]+)|([0-9]+)[a-zA-Z]+))(<\/a>)?/iu',
			function( $matches ) use ( $item, $member, $columnId, $obj ) {
				$url = \IPS\Http\Url::internal('app=hashtags&module=hashtags&controller=search&hashtag=' . $matches[9]);
				$itemColumnId = $item::$databaseColumnId;

				\IPS\Db::i()->insert(
					'hashtags_hashtags',
					[
						'hashtag' => $matches[9],
						'meta_item_id' => $item->$itemColumnId,
						'meta_app' => $item::$application,
						'meta_module' => $item::$module,
						'meta_member_id' => $member->member_id,
						'meta_node_id' => $item->container()->_id,
						'meta_comment_id' => $obj->$columnId,
						'created' => time(),
					]
				);

				return "{$matches[1]}<a href='{$url}'>{$matches[8]}</a>";
			},
			$comment
		);

		$obj->$columnContent = $comment;

		$obj->save();

		return $obj;
	}

	public function editContents( $newContent ) {

		$columnAuthor = static::$databaseColumnMap['author'];
		$author = $this->$columnAuthor;
		$item = $this->item();
		$node = $item->container();
		$itemColumnId = $item::$databaseColumnId;
		$columnId = static::$databaseColumnId;

		$newContent = preg_replace_callback( 
			'/(^|\s)(<a (([a-zA-Z]+)=(\u0027|\u0022)([^\u0022]*|[^\u0027]*)(\u0027|\u0022)*)>)?(#([a-zA-Z]([\w]+)|([0-9]+)[a-zA-Z]+))(<\/a>)?/iu',
			function( $matches ) use ( $node, $author, $item, $itemColumnId, $columnId ){
				$url = \IPS\Http\Url::internal('app=hashtags&module=hashtags&controller=search&hashtag=' . $matches[9]);

				$tagInserts[] = \IPS\Db::i()->insert(
					'hashtags_hashtags',
					[
						'hashtag' => $matches[9],
						'meta_app' => $item::$application,
						'meta_module' => $item::$module,
						'meta_member_id' => $author,
						'meta_node_id' => $node->_id,
						'meta_item_id' => $item->$itemColumnId,
						'meta_comment_id' => $this->$columnId,
						'created' => time(),
					]
				);

				return "{$matches[1]}<a href='{$url}'>{$matches[8]}</a>";
			}, 
			$newContent
		);

		parent::editContents( $newContent );
	}

}
