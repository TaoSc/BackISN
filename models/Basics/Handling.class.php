<?php
	namespace Basics;

	class Handling {
		public static function getList($condition = 'TRUE', $type = 'messages', $namespaces = 'Messages', $accessor = 'Message', $offsetLimit = false, $idsOnly = false, $ascending = false, $methodParams = null, ...$instanceParams) {
			$order = $ascending ? 'ASC' : 'DESC';
			if ($offsetLimit)
				$offsetLimit = ' LIMIT ' . $offsetLimit;

			if ($type === 'languages')
				$alias = 'code';
			elseif ($type === 'friend_requests')
				$alias = 'from_u';
			else
				$alias = null;

			$request = Site::getDB()->query('SELECT ' . (!empty($alias) ? ($alias . ' ') : null) . 'id FROM ' . $type . ' WHERE ' . $condition . ' ORDER BY id ' . $order . $offsetLimit);
			$ids = $request->fetchAll(\PDO::FETCH_ASSOC);

			if ($idsOnly)
				return $ids;
			else {
				$className = '\\' . $namespaces . '\\';
				if ($type === 'languages')
					$className .= 'Languages';
				elseif ($type === 'members_types')
					$className .= 'Type';
				else
					$className .= 'Single';

				$array = [];
				foreach ($ids as $element)
					$array[] = call_user_func_array([(new $className($element['id'], ...$instanceParams)), 'get' . $accessor], (array) $methodParams);
				return array_values(array_filter($array));
			}
		}

		public static function ipAddress() {
			$ip = $_SERVER['REMOTE_ADDR'];

			if (!empty($_SERVER['HTTP_CLIENT_IP']))
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];

			return $ip;
		}

		public static function recursiveArraySearch($needle, $haystack) {
			foreach ($haystack as $key => $value) {
				$currentKey = $key;
				if ($needle === $value OR (is_array($value) && Handling::recursiveArraySearch($needle, $value) !== false))
					return $currentKey;
			}
			return false;
		}

		public static function countEntries($table = 'messages', $conditions = 'TRUE') {
			$request = Site::getDB()->query('SELECT COUNT(*) total FROM ' . $table . ' WHERE ' . $conditions);

			return (int) $request->fetch(\PDO::FETCH_ASSOC)['total'];
		}

		public static function idFromSlug($slug, $tableName = 'messages', $column = 'slug', $noLanguage = true) {
			if ($noLanguage !== true) {
				global $clauses;

				return $clauses->getDB($tableName, $slug, $column, true, true, $noLanguage);
			}
			else {
				$request = Site::getDB()->prepare('SELECT id FROM ' . $tableName . ' WHERE ' . $column . ' = ?');
				$request->execute([$slug]);

				return $request->fetch(\PDO::FETCH_ASSOC)['id'];
			}
		}

		public static function latestId($from = 'messages', $select = 'id') {
			$request = Site::getDB()->query('SELECT ' . $select . ' FROM ' . $from . ' ORDER BY id DESC LIMIT 1');

			return $request->fetch(\PDO::FETCH_ASSOC)[$select];
		}
	}
