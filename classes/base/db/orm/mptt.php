<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2010 Kiall Mac Innes, Mathew Davies, and Mike Parkin
 * Copyright (c) 2012 Spadefoot
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software
 * and associated documentation files (the "Software"), to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT
 * NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 * This class represents an active record for an SQL database table and is
 * for handling a Modified Preorder Tree Traversal (MPTT).
 *
 * @package Leap
 * @category ORM
 * @version 2012-08-03
 *
 * @see https://github.com/kiall/kohana3-orm_mptt
 * @see http://dev.kohanaframework.org/projects/mptt
 *
 * @abstract
 */
abstract class Base_DB_ORM_MPTT extends DB_ORM_Model {

	/**
	 * @access public
	 * @var string parent id
	 */
	public $parent_id = 'parentID';

	/**
	 * @access public
	 * @var string
	 */
	public $title_column = 'name';

	/**
	 * @access public
	 * @var string
	 */
	public $link_column = 'alias';

	/**
	 * @access public
	 * @var string left column name.
	 */
	public $left_column = 'lft';

	/**
	 * @access public
	 * @var string right column name.
	 */
	public $right_column = 'rgt';

	/**
	 * @access public
	 * @var string level column name.
	 */
	public $level_column = 'lvl';

	/**
	 * @access public
	 * @var string scope column name.
	 **/
	public $scope_column = 'scope';

	/**
	 * Enable/Disable path calculation
	 *
	 */
	protected $path_calculation_enabled = FALSE;

	/**
	 * Full pre-calculated path
	 *
	 */
	public $path_column = 'path';

	/**
	 * Single path element
	 */
	public $path_part_column = 'path_part';

	/**
	 * Path separator
	 */
	public $path_separator = '/';

	/**
	 * TODO - extend this class so we can put in application specific settings
	 */
	/**
	 * The view to be used to create the unordered list
	 *
	 * @access public
	 * @var string
	 */
	public $ul_view = 'partial/asset/left_menu';

	/**
	 * New scope
	 * This also double as a new_root method allowing
	 * us to store multiple trees in the same table.
	 *
	 * @param integer $scope New scope to create.
	 * @param array $additional_fields
	 * @return boolean
	 **/
	public function new_scope($scope, array $additional_fields = array()) {
		// Make sure the specified scope doesn't already exist.
		$search = DB_ORM::select(get_class($this))->where($this->scope_column, '=', $scope)->query();

		if ($search->count() > 0 ) {
			return FALSE;
		}

		// Create a new root node in the new scope.
		$this->{$this->left_column} = 1;
		$this->{$this->right_column} = 2;
		$this->{$this->level_column} = 0;
		$this->{$this->scope_column} = $scope;

		// Other fields may be required.
		if ( ! empty($additional_fields)) {
			foreach ($additional_fields as $column => $value) {
				$this->{$column} = $value;
			}
		}

		parent::save();

		return $this;
	}

	/**
	 * Does the current node have children?
	 *
	 * @access public
	 * @return bool
	 */
	public function has_children() {
		return (($this->{$this->right_column} - $this->{$this->left_column}) > 1);
	}

	/**
	 * Is the current node a leaf node?
	 *
	 * @access public
	 * @return bool
	 */
	public function is_leaf() {
		return !$this->has_children();
	}

	/**
	 * Is the current node a descendant of the supplied node.
	 *
	 * @access public
	 * @param DB_ORM_MPTT $target Target
	 * @return bool
	 */
	public function is_descendant($target) {
		return ($this->{$this->left_column} > $target->{$this->left_column} AND $this->{$this->right_column} < $target->{$this->right_column} AND $this->{$this->scope_column} = $target->{$this->scope_column});
	}

	/**
	 * Is the current node a direct child of the supplied node?
	 *
	 * @access public
	 * @param DB_ORM_MPTT $target Target
	 * @return bool
	 */
	public function is_child($target) {
		return ($this->parent->{self::primary_key()} === $target->{self::primary_key()});
	}

	/**
	 * Is the current node the direct parent of the supplied node?
	 *
	 * @access public
	 * @param DB_ORM_MPTT $target Target
	 * @return bool
	 */
	public function is_parent($target) {
		return ($this->{self::primary_key()} === $target->parent->{self::primary_key()});
	}

	/**
	 * Is the current node a sibling of the supplied node
	 *
	 * @access public
	 * @param DB_ORM_MPTT $target Target
	 * @return bool
	 */
	public function is_sibling($target) {
		if ($this->{self::primary_key()} === $target->{self::primary_key()}) {
			return FALSE;
		}
		return ($this->parent->{self::primary_key()} === $target->parent->{self::primary_key()});
	}

	/**
	 * Is the current node a root node?
	 *
	 * @access public
	 * @return bool
	 */
	public function is_root() {
		return ($this->{$this->left_column} === 1);
	}

	/**
	 * Returns the root node.
	 *
	 * @access protected
	 * @return DB_ORM_MPTT
	 */
	public function root($scope = NULL) {
		if ($scope === NULL && $this->is_loaded()) {
			$scope = $this->{$this->scope_column};
		}
		elseif ($scope === NULL && !$this->is_loaded())
		{
			return FALSE;
		}

		return DB_ORM::select(get_class($this))->where($this->left_column, '=', 1)->where($this->scope_column, '=', $scope);
	}

	/**
	 * Returns the parent of the current node.
	 *
	 * @access public
	 * @return DB_ORM_MPTT
	 */
	public function parent() {
		return $this->parents()->where($this->level_column, '=', $this->{$this->level_column} - 1);
	}

	/**
	 * Returns the parents of the current node.
	 *
	 * @access public
	 * @param bool $root include the root node?
	 * @param string $direction direction to order the left column by.
	 * @return DB_ORM_MPTT
	 */
	public function parents($root = TRUE, $direction = 'ASC') {
		$parents = DB_ORM::select(get_class($this))
            ->where($this->left_column, '<=', $this->{$this->left_column})
            ->where($this->right_column, '>=', $this->{$this->right_column})
            ->where($this->scope_column, '=', $this->{$this->scope_column});

		foreach (static::primary_key() as $col) {
			$parents->where($col, '<>', $this->{$col});
		}

		$parents->order_by($this->left_column, $direction);

		if (!$root) {
			$parents->where($this->left_column, '!=', 1);
		}

		return $parents;
	}

	/**
	 * Returns the children of the current node.
	 *
	 * @access public
	 * @param bool $self include the current loaded node?
	 * @param string $direction direction to order the left column by.
	 * @return DB_ORM_MPTT
	 */
	public function children($self = FALSE, $direction = 'ASC') {
		if ($self) {
			return $this->descendants($self, $direction)->where($this->level_column, '<=', $this->{$this->level_column} + 1)->where($this->level_column, '>=', $this->{$this->level_column});
		}
		return $this->descendants($self, $direction)->where($this->level_column, '=', $this->{$this->level_column} + 1);
	}

	/**
	 * Returns the descendants of the current node.
	 *
	 * @access public
	 * @param bool $self include the current loaded node?
	 * @param string $direction direction to order the left column by.
	 * @return DB_ORM_MPTT
	 */
	public function descendants($self = FALSE, $direction = 'ASC') {
		$left_operator = $self ? '>=' : '>';
		$right_operator = $self ? '<=' : '<';

		return DB_ORM::select(get_class($this))
				->where($this->left_column, $left_operator, $this->{$this->left_column})
				->where($this->right_column, $right_operator, $this->{$this->right_column})
				->where($this->scope_column, '=', $this->{$this->scope_column})
				->order_by($this->left_column, $direction);
	}

	/**
	 * Returns the siblings of the current node
	 *
	 * @access public
	 * @param bool $self include the current loaded node?
	 * @param string $direction direction to order the left column by.
	 * @return DB_ORM_MPTT
	 */
	public function siblings($self = FALSE, $direction = 'ASC') {
		$siblings = DB_ORM::select(get_class($this))
				->where($this->left_column, '>', $this->parent->find()->{$this->left_column})
				->where($this->right_column, '<', $this->parent->find()->{$this->right_column})
				->where($this->scope_column, '=', $this->{$this->scope_column})
				->where($this->level_column, '=', $this->{$this->level_column})
				->order_by($this->left_column, $direction);

		if (!$self) {
			$siblings->where(self::primary_key(), '<>', $this->{self::primary_key()});
		}

		return $siblings;
	}

	/**
	 * Returns leaves under the current node.
	 *
	 * @access public
	 * @return DB_ORM_MPTT
	 */
	public function leaves() {
		return DB_ORM::select(get_class($this))
				->where($this->left_column, '=', new Database_Expression('(`' . $this->right_column . '` - 1)'))
				->where($this->left_column, '>=', $this->{$this->left_column})
				->where($this->right_column, '<=', $this->{$this->right_column})
				->where($this->scope_column, '=', $this->{$this->scope_column})
				->order_by($this->left_column, 'ASC');
	}

	/**
	 * Get Size
	 *
	 * @access protected
	 * @return integer
	 */
	protected function get_size() {
		return ($this->{$this->right_column} - $this->{$this->left_column}) + 1;
	}

	/**
	 * Create a gap in the tree to make room for a new node
	 *
	 * @access private
	 * @param integer $start start position.
	 * @param integer $size the size of the gap (default is 2).
	 */
	private function create_space($start, $size = 2) {
		// Update the right values
		$builder = DB_ORM::update(get_class($this))
			->set($this->right_column, DB_ORM::expr($this->right_column . ' + ' . $size))
			->where($this->right_column, '>=', $start)
			->where($this->scope_column, '=', $this->{$this->scope_column});
		$builder->execute();

		// Update the left values
		$builder = DB_ORM::update(get_class($this))
				->set($this->left_column, DB_ORM::expr($this->left_column . ' + ' . $size))
				->where($this->left_column, '>=', $start)
				->where($this->scope_column, '=', $this->{$this->scope_column});
		$builder->execute();
	}

	/**
	 * Closes a gap in a tree. Mainly used after a node has
	 * been removed.
	 *
	 * @access private
	 * @param integer $start start position.
	 * @param integer $size the size of the gap (default is 2).
	 */
	private function delete_space($start, $size = 2) {
		// Update the left values
		$builder = DB_ORM::update(get_class($this))
				->set($this->left_column, DB_ORM::expr($this->left_column . ' - ' . $size))
				->where($this->left_column, '>=', $start)
				->where($this->scope_column, '=', $this->{$this->scope_column});
		$builder->execute();

		// Update the right values
		$builder = DB_ORM::update(get_class($this))
				->set($this->right_column, DB_ORM::expr($this->right_column . ' - ' . $size))
				->where($this->right_column, '>=', $start)
				->where($this->scope_column, '=', $this->{$this->scope_column});
		$builder->execute();
	}

	/**
	 * Insert a node
	 *
	 * @param $target int The id of the record you want to insert after
	 * @param $copy_left_from int
	 * @param $left_offset int
	 * @param $level_offset int
	 * @return Base_DB_ORM_MPTT|bool
	 */
	protected function insert($target, $copy_left_from, $left_offset, $level_offset) {
		// Insert should only work on new nodes.. if its already in the tree it needs to be moved!
		if ($this->metadata['saved']) {
			return FALSE;
		}

		if (!$target instanceof $this) {
			$target = DB_ORM::model(get_class($this), $target);
		}
		else {
			$target->load(); // Ensure we're using the latest version of $target
		}

		$this->{$this->left_column} = $target->{$copy_left_from} + $left_offset;
		$this->{$this->right_column} = $this->{$this->left_column} + 1;
		$this->{$this->level_column} = $target->{$this->level_column} + $level_offset;
		$this->{$this->scope_column} = $target->{$this->scope_column};

		$this->create_space($this->{$this->left_column});

		parent::save(TRUE);

		if ($this->path_calculation_enabled) {
			$this->update_path();
			parent::save(TRUE);
		}

		return $this;
	}

	/**
	 * Inserts a new node to the left of the target node.
	 *
	 * @access public
	 * @param DB_ORM_MPTT $target target node id or DB_ORM_MPTT object.
	 * @return DB_ORM_MPTT
	 */
	public function insert_as_first_child($target) {
		return $this->insert($target, $this->left_column, 1, 1);
	}

	/**
	 * Inserts a new node to the right of the target node.
	 *
	 * @access public
	 * @param DB_ORM_MPTT $target target node id or DB_ORM_MPTT object.
	 * @return DB_ORM_MPTT
	 */
	public function insert_as_last_child($target) {
		return $this->insert($target, $this->right_column, 0, 1);
	}

	/**
	 * Inserts a new node as a previous sibling of the target node.
	 *
	 * @access public
	 * @param DB_ORM_MPTT|integer $target target node id or DB_ORM_MPTT object.
	 * @return DB_ORM_MPTT
	 */
	public function insert_as_prev_sibling($target) {
		return $this->insert($target, $this->left_column, 0, 0);
	}

	/**
	 * Inserts a new node as the next sibling of the target node.
	 *
	 * @access public
	 * @param DB_ORM_MPTT|integer $target target node id or DB_ORM_MPTT object.
	 * @return DB_ORM_MPTT
	 */
	public function insert_as_next_sibling($target) {
		return $this->insert($target, $this->right_column, 1, 0);
	}

	/**
	 * Overloaded save method
	 *
	 * @access public
	 * @return DB_ORM_MPTT|bool
	 */
	public function save($reload = FALSE) {
		if ($this->is_loaded() === TRUE) {
			return parent::save();
		}
		return FALSE;
	}

	/**
	 * Removes a node and it's descendants.
	 *
	 * $usless_param prevents a strict error that breaks PHPUnit like hell!
	 * @access public
	 * @param bool $descendants remove the descendants?
	 * @return bool
	 */
	public function delete($reset = FALSE) {
		$this->load();

        DB_ORM::delete(get_class($this))
            ->where($this->left_column, '>=', $this->{$this->left_column})
            ->where($this->right_column, '<=', $this->{$this->right_column})
            ->where($this->scope_column, '=', $this->{$this->scope_column})
            ->execute();

        $this->delete_space($this->{$this->left_column}, $this->get_size());

		return TRUE;
	}


	/**
	 * Move to First Child
	 *
	 * Moves the current node to the first child of the target node.
	 *
	 * @param DB_ORM_MPTT|integer $target target node id or DB_ORM_MPTT object.
	 * @return DB_ORM_MPTT
	 */
	public function move_to_first_child($target) {
		return $this->move($target, TRUE, 1, 1, TRUE);
	}

	/**
	 * Move to Last Child
	 *
	 * Moves the current node to the last child of the target node.
	 *
	 * @param DB_ORM_MPTT|integer $target target node id or DB_ORM_MPTT object.
	 * @return DB_ORM_MPTT
	 */
	public function move_to_last_child($target) {
		return $this->move($target, FALSE, 0, 1, TRUE);
	}

	/**
	 * Move to Previous Sibling.
	 *
	 * Moves the current node to the previous sibling of the target node.
	 *
	 * @param DB_ORM_MPTT|integer $target target node id or DB_ORM_MPTT object.
	 * @return DB_ORM_MPTT
	 */
	public function move_to_prev_sibling($target) {
		return $this->move($target, TRUE, 0, 0, FALSE);
	}

	/**
	 * Move to Next Sibling.
	 *
	 * Moves the current node to the next sibling of the target node.
	 *
	 * @param DB_ORM_MPTT|integer $target target node id or DB_ORM_MPTT object.
	 * @return DB_ORM_MPTT
	 */
	public function move_to_next_sibling($target) {
		return $this->move($target, FALSE, 1, 0, FALSE);
	}

	/**
	 * Move
	 *
	 * @param DB_ORM_MPTT|integer $target target node id or DB_ORM_MPTT object.
	 * @param bool $left_column use the left column or right column from target
	 * @param integer $left_offset left value for the new node position.
	 * @param integer $level_offset level
	 * @param bool allow this movement to be allowed on the root node
	 */
	protected function move($target, $left_column, $left_offset, $level_offset, $allow_root_target) {
		if (!$this->is_loaded()) {
			return FALSE;
		}

		// Make sure we have the most up to date version of this
		$this->load();

		if (!$target instanceof $this) {
			$target = DB_ORM::model(get_class($this), $target);

			if (!$target->is_loaded()) {
				return FALSE;
			}
		}
		else {
			$target->load();
		}

		// Stop $this being moved into a descendant or disallow if target is root
		if ($target->is_descendant($this) OR ($allow_root_target === FALSE AND $target->is_root())) {
			return FALSE;
		}

		$left_offset = ($left_column === TRUE ? $target->{$this->left_column} : $target->{$this->right_column}) + $left_offset;
		$level_offset = $target->{$this->level_column} - $this->{$this->level_column} + $level_offset;

		$size = $this->get_size();

		$this->create_space($left_offset, $size);

		// if node is moved to a position in the tree "above" its current placement
		// then its lft/rgt may have been altered by create_space
		$this->load();

		$offset = ($left_offset - $this->{$this->left_column});

		// Update the values
		$builder = DB_ORM::update(get_class($this))
			->set($this->left_column, DB_ORM::expr($this->left_column . ' + ' . $offset))
			->set($this->right_column, DB_ORM::expr($this->right_column . ' + ' . $offset))
			->set($this->level_column, DB_ORM::expr($this->level_column . ' + ' . $level_offset))
			->set($this->scope_column, $target->{$this->scope_column})
			->where($this->left_column, '>=', $this->{$this->left_column})
			->where($this->right_column, '<=', $this->{$this->right_column})
			->where($this->scope_column, '=', $this->{$this->scope_column});

		$builder->execute();

		$this->delete_space($this->{$this->left_column}, $size);

		if ($this->path_calculation_enabled) {
			$this->update_path();
			parent::save();
		}

		return $this;
	}

	/**
	 *
	 * @access public
	 * @param $column - Which field to get.
	 * @return mixed
	 */
	public function __get($column) {
		switch ($column) {
			case 'parent':
				return $this->parent();
			case 'parents':
				return $this->parents();
			case 'children':
				return $this->children();
			case 'siblings':
				return $this->siblings();
			case 'root':
				return $this->root();
			case 'leaves':
				return $this->leaves();
			case 'descendants':
				return $this->descendants();
			default:
				return parent::__get($column);
		}
	}

	/**
	 * Verify the tree is in good order
	 *
	 * This functions speed is irrelevant - its really only for debugging and unit tests
	 *
	 * @todo Look for any nodes no longer contained by the root node.
	 * @todo Ensure every node has a path to the root via ->parents();
	 * @access public
	 * @return boolean
	 */
	public function verify_tree() {
		foreach ($this->get_scopes() as $scope) {
			if (!$this->verify_scope($scope->{$this->scope_column})) {
				return FALSE;
			}
		}
		return TRUE;
	}

    // TODO... redo this so its proper :P and open it public
    // used by verify_tree()
    private function get_scopes() {
		$result = DB_SQL::select('default')
            ->column(DB_SQL::expr('DISTINCT(' . $this->scope_column . ')'))
			->from($this->table())
            ->query();
        return $result;
	}

    // TODO Use model's data source, not default
    // TODO Fixed instance variables references
	public function verify_scope($scope) {
		$root = $this->root($scope);

		$end = $root->{$this->right_column};

		// Find nodes that have slipped out of bounds.
		$result = DB_SQL::select('default')
            ->column(DB_SQL::expr('count(*)'), 'count')
            ->from($this->_table_name)
            ->where($this->scope_column, '=', $root->{$this->scope_column})
            ->where_block('(')
            ->where($this->left_column, '>', $end)
            ->where($this->right_column, '>', $end, 'OR')
            ->where_block(')')
            ->query();

        if ($result[0]->count > 0) {
			return FALSE;
		}

		// Find nodes that have the same left and right value
        $result = DB_SQL::select('default')
            ->column(DB_SQL::expr('count(*)'), 'count')
            ->from($this->_table_name)
            ->where($this->scope_column, '=', $root->{$this->scope_column})
            ->where($this->left_column, '=', $this->right_column)
            ->query();

		if ($result[0]->count > 0) {
			return FALSE;
		}

		// Find nodes that right value is less than the left value
        $result = DB_SQL::select('default')
            ->column(DB_SQL::expr('count(*)'), 'count')
            ->from($this->_table_name)
            ->where($this->scope_column, '=', $root->{$this->scope_column})
            ->where($this->left_column, '>', $this->right_column)
            ->query();

        if ($result[0]->count > 0) {
			return FALSE;
		}

		// Make sure no 2 nodes share a left/right value
		$i = 1;
		while ($i <= $end) {
            $result = DB_SQL::select('default')
                ->column(DB_SQL::expr('count(*)'), 'count')
                ->from($this->_table_name)
                ->where($this->scope_column, '=', $root->{$this->scope_column})
                ->where_block('(')
                ->where($this->left_column, '=', $i)
                ->where($this->right_column, '=', $i, 'OR')
                ->where_block(')')
                ->query();

			if ($result[0]->count > 1) {
				return FALSE;
			}

			$i++;
		}

		// Check to ensure that all nodes have a "correct" level
		//TODO

		return TRUE;
	}

    // TODO Replace find_all() with LEAP's equivalent
	public function update_path() {
		$path = '';

		$parents = $this->parents(FALSE)
			->find_all();

		foreach ($parents as $parent) {
			$path .= $this->path_separator . trim($parent->{$this->path_part_column});
		}

		$path .= $this->path_separator . trim($this->{$this->path_part_column});

		$path = trim($path, $this->path_separator);

		$this->{$this->path_column} = $path;

		return $this;
	}

	/**
	 * This function returns a multidimensional array
	 *
	 * @return mixed
	 */
	public function as_multi_array() {
		$descendants = $this->descendants(TRUE)->query();

		$descendants_array = array();
		foreach ($descendants as $d) {
			$descendants_array[] = $d->data();
		}

		$stack = array();

		for ($i = 0; $i < count($descendants_array); $i++) {
			$d = &$descendants_array[$i];
			$d['Children'] = array();

			while (count($stack) > 0 && $stack[count($stack) - 1][$this->right_column] < $d[$this->right_column]) {
				array_pop($stack);
			}

			if (count($stack) > 0) {
				$stack[count($stack) - 1]['Children'][] = &$d;
			}

			$stack[] = &$d;
		}
		
		return $stack[0];
	}

	/**
	 * This function return an HTML unordered list
	 *
	 * @return string
	 */
	public function as_ul() {
		$descendants = $this->descendants(TRUE)->query();

		$tree = array();
		foreach ($descendants as $d) {
			$tree[] = $d->data();
		}

		$result = View::factory($this->ul_view)
			->bind('mptt', $this)
			->bind('tree', $tree);

		return $result;
	}

	/**
	 * This function returns an array of just the fields
	 *
	 * @return array
	 */
	public function data() {
		$buffer = array();
		foreach ($this->fields as $col => $field) {
			$buffer[$col] = $field->value;
		}
		return $buffer;
	}

}
?>