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

		if( \in_array( 'IPS\Content\Searchable', class_implements( \get_called_class() ) ) ) {
			$columnContent = static::$databaseColumnMap['content'];
			$columnId = static::$databaseColumnId;
			$comment = preg_replace_callback(
				'/(^|\b|\s|>)(<(a|b|i|u|s|em|strong)([\sa-z0-9=\x{0022}\x{0027}:\/\.?&\-_]+)?>)?(#([\p{L}_]+|([0-9]*)[\p{L}_]+))(<\/(a|b|i|u|s|em|strong)>)?(<\/|\b|\s|!|\?|\.|,|$)/iu',
				function( $matches ) use ( $item, $member, $columnId, $obj ) {
					$url = \IPS\Http\Url::internal('app=hashtags&module=hashtags&controller=search&hashtag=' . $matches[6]);
					$itemColumnId = $item::$databaseColumnId;

					\IPS\Db::i()->insert(
						'hashtags_hashtags',
						[
							'hashtag' => $matches[6],
							'meta_item_id' => $item->$itemColumnId,
							'meta_app' => $item::$application,
							'meta_module' => $item::$module,
							'meta_member_id' => $member->member_id,
							'meta_node_id' => $item->container()->_id,
							'meta_comment_id' => $obj->$columnId,
							'created' => time(),
						]
					);
					if( $matches[3] === 'a' ) {
						return "{$matches[1]}<a href='{$url}'>{$matches[5]}</a>{$matches[10]}";
					} else {
						return "{$matches[1]}{$matches[2]}<a href='{$url}'>{$matches[5]}</a>{$matches[8]}{$matches[10]}";
					}
				},
				$comment
			);

			$obj->$columnContent = $comment;

			$obj->save();
		}

		return $obj;
	}

	public function editContents( $newContent ) {

		if( \in_array( 'IPS\Content\Searchable', class_implements( \get_called_class() ) ) ) {

			$columnAuthor = static::$databaseColumnMap['author'];
			$author = $this->$columnAuthor;
			$item = $this->item();
			$node = $item->container();
			$itemColumnId = $item::$databaseColumnId;
			$columnId = static::$databaseColumnId;

			\IPS\Db::i()->delete(
				'hashtags_hashtags',
				[
					"meta_item_id=? AND meta_app=? AND meta_module=? AND meta_member_id=? AND meta_node_id=? AND meta_comment_id=?",
					$item->$itemColumnId,
					$item::$application,
					$item::$module,
					$author,
					$node->_id,
					$this->$columnId,
				]
			);

			$newContent = preg_replace_callback( 
				'/(^|\b|\s|>)(<(a|b|i|u|s|em|strong)([\sa-z0-9=\x{0022}\x{0027}:\/\.?&\-_]+)?>)?(#([\p{L}_]+|([0-9]*)[\p{L}_]+))(<\/(a|b|i|u|s|em|strong)>)?(<\/|\b|\s|!|\?|\.|,|$)/iu',
				function( $matches ) use ( $node, $author, $item, $itemColumnId, $columnId ){
					$url = \IPS\Http\Url::internal('app=hashtags&module=hashtags&controller=search&hashtag=' . $matches[6]);
					
					\IPS\Db::i()->insert(
						'hashtags_hashtags',
						[
							'hashtag' => $matches[6],
							'meta_app' => $item::$application,
							'meta_module' => $item::$module,
							'meta_member_id' => $author,
							'meta_node_id' => $node->_id,
							'meta_item_id' => $item->$itemColumnId,
							'meta_comment_id' => $this->$columnId,
							'created' => time(),
						]
					);

					if( $matches[3] === 'a' ) {
						return "{$matches[1]}<a href='{$url}'>{$matches[5]}</a>{$matches[10]}";
					} else {
						return "{$matches[1]}{$matches[2]}<a href='{$url}'>{$matches[5]}</a>{$matches[8]}{$matches[10]}";
					}
				}, 
				$newContent
			);
		}

		parent::editContents( $newContent );
	}

}
