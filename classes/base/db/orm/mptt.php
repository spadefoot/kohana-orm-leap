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
 * @version 2012-08-21
 *
 * @see http://dev.kohanaframework.org/projects/mptt
 * @see https://github.com/kiall/kohana3-orm_mptt
 * @see https://github.com/smgladkovskiy/jelly-mptt
 * @see https://github.com/banks/sprig-mptt
 *
 * @abstract
 */
abstract class Base_DB_ORM_MPTT extends DB_ORM_Model {

	/**
	 * This variable stores the parent id.
	 *
	 * @access public
	 * @var string
	 */
	public $parent_id = 'parentID';

	/**
	 * This variable stores the title column.
	 *
	 * @access public
	 * @var string
	 */
	public $title_column = 'name';

	/**
	 * This variable stores the link column.
	 *
	 * @access public
	 * @var string
	 */
	public $link_column = 'alias';

	/**
	 * This variable stores the name of the left column.
	 *
	 * @access public
	 * @var string
	 */
	public $left_column = 'lft';

	/**
	 * This variable stores the name of the right column.
	 *
	 * @access public
	 * @var string
	 */
	public $right_column = 'rgt';

	/**
	 * This variable stores the name of the level column.
	 *
	 * @access public
	 * @var string
	 */
	public $level_column = 'lvl';

	/**
	 * This variable stores the name of the scope column.
	 *
	 * @access public
	 * @var string
	 **/
	public $scope_column = 'scope';

	/**
	 * This variable stores whether path calculation is enabled/disabled.
	 *
	 * @access protected
	 * @var boolean
	 */
	protected $path_calculation_enabled = FALSE;

	/**
	 * This variable stores the full pre-calculated path.
	 *
	 * @access public
	 * @var string
	 */
	public $path_column = 'path';

	/**
	 * This variable stores the single path element.
	 *
	 * @access public
	 * @var string
	 */
	public $path_part_column = 'path_part';

	/**
	 * This variable stores the path separator to be used.
	 *
	 * @access public
	 * @var char
	 */
	public $path_separator = '/';

	/**
	 * The view to be used to create the unordered list
	 *
	 * @access public
	 * @var string
	 */
	public $ul_view = 'partial/asset/left_menu';

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
	 * This function returns a multidimensional array
	 *
	 * @return mixed
	 */
	public function as_multi_array() {
		$descendants = $this->descendants(TRUE)->query();

		$descendants_array = array();
		foreach ($descendants as $descendant) {
			$descendants_array[] = $descendant->data();
		}

		$stack = array();

		for ($i = 0; $i < count($descendants_array); $i++) {
			$d = &$descendants_array[$i];
			$d['Children'] = array();

			while ((count($stack) > 0) AND ($stack[count($stack) - 1][$this->right_column] < $d[$this->right_column])) {
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
		$descendants = $this->descendants(TRUE)
            ->query();

		$tree = array();
		foreach ($descendants as $descendant) {
			$tree[] = $descendant->data();
		}

		$result = View::factory($this->ul_view)
			->bind('mptt', $this)
			->bind('tree', $tree);

		return $result;
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
			return $this->descendants($self, $direction)
				->where($this->level_column, DB_SQL_Operator::_LESS_THAN_OR_EQUAL_TO_, $this->{$this->level_column} + 1)
				->where($this->level_column, DB_SQL_Operator::_GREATER_THAN_OR_EQUAL_TO_, $this->{$this->level_column});
		}
		return $this->descendants($self, $direction)
			->where($this->level_column, DB_SQL_Operator::_EQUAL_TO_, $this->{$this->level_column} + 1);
	}

	/**
	 * Create a gap in the tree to make room for a new node
	 *
	 * @access protected
	 * @param integer $start start position.
	 * @param integer $size the size of the gap (default is 2).
	 */
	protected function create_space($start, $size = 2) {
		// Update the right values
		DB_ORM::update(get_class($this))
			->set($this->right_column, DB_ORM::expr($this->right_column . ' + ' . $size))
			->where($this->right_column, DB_SQL_Operator::_GREATER_THAN_OR_EQUAL_TO_, $start)
			->where($this->scope_column, DB_SQL_Operator::_EQUAL_TO_, $this->{$this->scope_column})
			->execute();

		// Update the left values
		DB_ORM::update(get_class($this))
			->set($this->left_column, DB_ORM::expr($this->left_column . ' + ' . $size))
			->where($this->left_column, DB_SQL_Operator::_GREATER_THAN_OR_EQUAL_TO_, $start)
			->where($this->scope_column, DB_SQL_Operator::_EQUAL_TO_, $this->{$this->scope_column})
			->execute();
	}

	/**
	 * This function returns an array of just the fields
	 *
	 * @return array
	 */
	public function data() {
		$buffer = array();
		foreach ($this->fields as $name => $field) {
			$buffer[$name] = $field->value;
		}
		return $buffer;
	}

	/**
	 * Removes a node and it's descendants.
	 *
	 * @access public
	 * @param boolean $descendants remove the descendants
	 * @return boolean
	 */
	public /*override*/ function delete($reset = FALSE) {
		$this->load();

		DB_ORM::delete(get_class($this))
			->where($this->left_column, DB_SQL_Operator::_GREATER_THAN_OR_EQUAL_TO_, $this->{$this->left_column})
			->where($this->right_column, DB_SQL_Operator::_LESS_THAN_OR_EQUAL_TO_, $this->{$this->right_column})
			->where($this->scope_column, DB_SQL_Operator::_EQUAL_TO_, $this->{$this->scope_column})
			->execute();

		$this->delete_space($this->{$this->left_column}, $this->get_size());

		return TRUE;
	}

	/**
	 * Closes a gap in a tree. Mainly used after a node has
	 * been removed.
	 *
	 * @access protected
	 * @param integer $start start position.
	 * @param integer $size the size of the gap (default is 2).
	 */
	protected function delete_space($start, $size = 2) {
		// Update the left values
		DB_ORM::update(get_class($this))
			->set($this->left_column, DB_ORM::expr($this->left_column . ' - ' . $size))
			->where($this->left_column, DB_SQL_Operator::_GREATER_THAN_OR_EQUAL_TO_, $start)
			->where($this->scope_column, DB_SQL_Operator::_EQUAL_TO_, $this->{$this->scope_column})
			->execute();

		// Update the right values
		DB_ORM::update(get_class($this))
			->set($this->right_column, DB_ORM::expr($this->right_column . ' - ' . $size))
			->where($this->right_column, DB_SQL_Operator::_GREATER_THAN_OR_EQUAL_TO_, $start)
			->where($this->scope_column, DB_SQL_Operator::_EQUAL_TO_, $this->{$this->scope_column})
			->execute();
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
		$left_operator = ($self) ? DB_SQL_Operator::_GREATER_THAN_OR_EQUAL_TO_ : DB_SQL_Operator::_GREATER_THAN_;
		$right_operator = ($self) ? DB_SQL_Operator::_LESS_THAN_OR_EQUAL_TO_ : DB_SQL_Operator::_LESS_THAN_;

		return DB_ORM::select(get_class($this))
			->where($this->left_column, $left_operator, $this->{$this->left_column})
			->where($this->right_column, $right_operator, $this->{$this->right_column})
			->where($this->scope_column, DB_SQL_Operator::_EQUAL_TO_, $this->{$this->scope_column})
			->order_by($this->left_column, $direction);
	}

	// TODO redo this so its proper :P and open it public
	// used by verify_tree()
	protected function get_scopes() {
		$result = DB_SQL::select(static::data_source())
			->column(DB_SQL::expr('DISTINCT(' . $this->scope_column . ')'))
			->from(static::table())
			->query();
		return $result;
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
	 * Does the current node have children?
	 *
	 * @access public
	 * @return bool
	 */
	public function has_children() {
		return (($this->{$this->right_column} - $this->{$this->left_column}) > 1);
	}

	/**
	 * Insert a node
	 *
	 * @param $target int The id of the record you want to insert after
	 * @param $copy_left_from int
	 * @param $left_offset int
	 * @param $level_offset int
	 * @return DB_ORM_MPTT|bool
	 */
	protected function insert($target, $copy_left_from, $left_offset, $level_offset) {
		// Insert should only work on new nodes.. if its already in the tree it needs to be moved!
		if ($this->metadata['saved']) {
			return FALSE;
		}

		if ( ! ($target instanceof $this)) {
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
	 * Is the current node a direct child of the supplied node?
	 *
	 * @access public
	 * @param DB_ORM_MPTT $target Target
	 * @return bool
	 */
	public function is_child(DB_ORM_MPTT $target) {
		return ($this->parent->{static::primary_key()} === $target->{static::primary_key()});
	}

	/**
	 * Is the current node a descendant of the supplied node.
	 *
	 * @access public
	 * @param DB_ORM_MPTT $target Target
	 * @return bool
	 */
	public function is_descendant(DB_ORM_MPTT $target) {
		return (($this->{$this->left_column} > $target->{$this->left_column}) AND ($this->{$this->right_column} < $target->{$this->right_column}) AND ($this->{$this->scope_column} = $target->{$this->scope_column}));
	}

	/**
	 * Is the current node a leaf node?
	 *
	 * @access public
	 * @return bool
	 */
	public function is_leaf() {
		return ! $this->has_children();
	}

	/**
	 * Is the current node the direct parent of the supplied node?
	 *
	 * @access public
	 * @param DB_ORM_MPTT $target Target
	 * @return bool
	 */
	public function is_parent(DB_ORM_MPTT $target) {
		return ($this->{static::primary_key()} === $target->parent->{static::primary_key()});
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
	 * Is the current node a sibling of the supplied node
	 *
	 * @access public
	 * @param DB_ORM_MPTT $target Target
	 * @return bool
	 */
	public function is_sibling(DB_ORM_MPTT $target) {
		$primary_key = static::primary_key();
		if ($this->{$primary_key} === $target->{$primary_key}) {
			return FALSE;
		}
		return ($this->parent->{$primary_key} === $target->parent->{$primary_key});
	}

	/**
	 * Returns leaves under the current node.
	 *
	 * @access public
	 * @return DB_ORM_MPTT
	 */
	public function leaves() {
		return DB_ORM::select(get_class($this))
			->where($this->left_column, DB_SQL_Operator::_EQUAL_TO_, DB_ORM::expr('(`' . $this->right_column . '` - 1)'))
			->where($this->left_column, DB_SQL_Operator::_GREATER_THAN_OR_EQUAL_TO_, $this->{$this->left_column})
			->where($this->right_column, DB_SQL_Operator::_LESS_THAN_OR_EQUAL_TO_, $this->{$this->right_column})
			->where($this->scope_column, DB_SQL_Operator::_EQUAL_TO_, $this->{$this->scope_column})
			->order_by($this->left_column, 'ASC');
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
		if ( ! $this->is_loaded()) {
			return FALSE;
		}

		// Make sure we have the most up to date version of this
		$this->load();

		if ( ! ($target instanceof $this)) {
			$target = DB_ORM::model(get_class($this), $target);
			if ( ! $target->is_loaded()) {
				return FALSE;
			}
		}
		else {
			$target->load();
		}

		// Stop $this being moved into a descendant or disallow if target is root
		if ($target->is_descendant($this) OR (($allow_root_target === FALSE) AND $target->is_root())) {
			return FALSE;
		}

		$left_offset = (($left_column === TRUE) ? $target->{$this->left_column} : $target->{$this->right_column}) + $left_offset;
		$level_offset = $target->{$this->level_column} - $this->{$this->level_column} + $level_offset;

		$size = $this->get_size();

		$this->create_space($left_offset, $size);

		// if node is moved to a position in the tree "above" its current placement
		// then its lft/rgt may have been altered by create_space
		$this->load();

		$offset = ($left_offset - $this->{$this->left_column});

		// Update the values
		DB_ORM::update(get_class($this))
			->set($this->left_column, DB_ORM::expr($this->left_column . ' + ' . $offset))
			->set($this->right_column, DB_ORM::expr($this->right_column . ' + ' . $offset))
			->set($this->level_column, DB_ORM::expr($this->level_column . ' + ' . $level_offset))
			->set($this->scope_column, $target->{$this->scope_column})
			->where($this->left_column, DB_SQL_Operator::_GREATER_THAN_OR_EQUAL_TO_, $this->{$this->left_column})
			->where($this->right_column, DB_SQL_Operator::_LESS_THAN_OR_EQUAL_TO_, $this->{$this->right_column})
			->where($this->scope_column, DB_SQL_Operator::_EQUAL_TO_, $this->{$this->scope_column})
			->execute();

		$this->delete_space($this->{$this->left_column}, $size);

		if ($this->path_calculation_enabled) {
			$this->update_path();
			parent::save();
		}

		return $this;
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
	 * New scope
	 * This also double as a new_root method allowing
	 * us to store multiple trees in the same table.
	 *
	 * @param integer $scope New scope to create.
	 * @param array $additional_fields
	 * @return boolean
	 **/
	public function new_scope($scope, Array $additional_fields = array()) {
		// Make sure the specified scope doesn't already exist.
		$search = DB_ORM::select(get_class($this))
			->where($this->scope_column, DB_SQL_Operator::_EQUAL_TO_, $scope)
			->query();

		if ($search->count() > 0) {
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
	 * Returns the parent of the current node.
	 *
	 * @access public
	 * @return DB_ORM_MPTT
	 */
	public function parent() {
		return $this->parents()->where($this->level_column, DB_SQL_Operator::_EQUAL_TO_, $this->{$this->level_column} - 1);
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
			->where($this->left_column, DB_SQL_Operator::_LESS_THAN_OR_EQUAL_TO_, $this->{$this->left_column})
			->where($this->right_column, DB_SQL_Operator::_GREATER_THAN_OR_EQUAL_TO_, $this->{$this->right_column})
			->where($this->scope_column, DB_SQL_Operator::_EQUAL_TO_, $this->{$this->scope_column});

		foreach (static::primary_key() as $col) {
			$parents->where($col, DB_SQL_Operator::_NOT_EQUIVALENT_, $this->{$col});
		}

		$parents->order_by($this->left_column, $direction);

		if ( ! $root) {
			$parents->where($this->left_column, DB_SQL_Operator::_NOT_EQUAL_TO_, 1);
		}

		return $parents;
	}

	/**
	 * Returns the root node.
	 *
	 * @access public
	 * @return DB_ORM_MPTT
	 */
	public function root($scope = NULL) {
		if (($scope === NULL) AND $this->is_loaded()) {
			$scope = $this->{$this->scope_column};
		}
		else if (($scope === NULL) AND ! $this->is_loaded()) {
			return FALSE;
		}
		return DB_ORM::select(get_class($this))
			->where($this->left_column, DB_SQL_Operator::_EQUAL_TO_, 1)
			->where($this->scope_column, DB_SQL_Operator::_EQUAL_TO_, $scope);
	}

	/**
	 * Overloaded save method
	 *
	 * @access public
	 * @return DB_ORM_MPTT|bool
	 */
	public /*override*/ function save($reload = FALSE, $mode = NULL) {
		if ($this->is_loaded() === TRUE) {
			return parent::save($reload, $mode);
		}
		return FALSE;
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
			->where($this->left_column, DB_SQL_Operator::_GREATER_THAN_, $this->parent->fetch(0)->{$this->left_column})
			->where($this->right_column, DB_SQL_Operator::_LESS_THAN_, $this->parent>fetch(0)->{$this->right_column})
			->where($this->scope_column, DB_SQL_Operator::_EQUAL_TO_, $this->{$this->scope_column})
			->where($this->level_column, DB_SQL_Operator::_EQUAL_TO_, $this->{$this->level_column})
			->order_by($this->left_column, $direction);

		if ( ! $self) {
			$siblings->where(static::primary_key(), DB_SQL_Operator::_NOT_EQUIVALENT_, $this->{static::primary_key()});
		}

		return $siblings;
	}

	public function update_path() {
		$path = '';

		$parents = $this->parents(FALSE)
			->query();

		foreach ($parents as $parent) {
			$path .= $this->path_separator . trim($parent->{$this->path_part_column});
		}

		$path .= $this->path_separator . trim($this->{$this->path_part_column});

		$path = trim($path, $this->path_separator);

		$this->{$this->path_column} = $path;

		return $this;
	}

	// TODO Use model's data source, not default
	// TODO Fixed instance variables references
	public function verify_scope($scope) {
		$root = $this->root($scope);

		$end = $root->{$this->right_column};

		// Find nodes that have slipped out of bounds.
		$result = DB_SQL::select(static::data_source())
			->column(DB_SQL::expr('COUNT(*)'), 'count')
			->from(static::table())
			->where($this->scope_column, DB_SQL_Operator::_EQUAL_TO_, $root->{$this->scope_column})
			->where_block(DB_SQL_Builder::_OPENING_PARENTHESIS_)
			->where($this->left_column, DB_SQL_Operator::_GREATER_THAN_, $end)
			->where($this->right_column, DB_SQL_Operator::_GREATER_THAN_, $end, DB_SQL_Connector::_OR_)
			->where_block(DB_SQL_Builder::_CLOSING_PARENTHESIS_)
			->query();

		if ($result[0]->count > 0) {
			return FALSE;
		}

		// Find nodes that have the same left and right value
		$result = DB_SQL::select(static::data_source())
			->column(DB_SQL::expr('COUNT(*)'), 'count')
			->from(static::table())
			->where($this->scope_column, DB_SQL_Operator::_EQUAL_TO_, $root->{$this->scope_column})
			->where($this->left_column, DB_SQL_Operator::_EQUAL_TO_, $this->right_column)
			->query();

		if ($result[0]->count > 0) {
			return FALSE;
		}

		// Find nodes that right value is less than the left value
		$result = DB_SQL::select(static::data_source())
			->column(DB_SQL::expr('COUNT(*)'), 'count')
			->from(static::table())
			->where($this->scope_column, DB_SQL_Operator::_EQUAL_TO_, $root->{$this->scope_column})
			->where($this->left_column, DB_SQL_Operator::_GREATER_THAN_, $this->right_column)
			->query();

		if ($result[0]->count > 0) {
			return FALSE;
		}

		// Make sure no 2 nodes share a left/right value
		$i = 1;
		while ($i <= $end) {
			$result = DB_SQL::select(static::data_source())
				->column(DB_SQL::expr('COUNT(*)'), 'count')
				->from(static::table())
				->where($this->scope_column, DB_SQL_Operator::_EQUAL_TO_, $root->{$this->scope_column})
				->where_block(DB_SQL_Builder::_OPENING_PARENTHESIS_)
				->where($this->left_column, DB_SQL_Operator::_EQUAL_TO_, $i)
				->where($this->right_column, DB_SQL_Operator::_EQUAL_TO_, $i, DB_SQL_Connector::_OR_)
				->where_block(DB_SQL_Builder::_CLOSING_PARENTHESIS_)
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
			if ( ! $this->verify_scope($scope->{$this->scope_column})) {
				return FALSE;
			}
		}
		return TRUE;
	}

}
?>