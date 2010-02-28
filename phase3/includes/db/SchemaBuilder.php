<?php
/**
 * SchemaBuilder - Uses definition in Schema.php to create a DBMS-specific
 * schema for MediaWiki
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA
 *
 * @author Chad Horohoe <chad@anyonecanedit.org>
 * @todo Handle custom table options, eg: MyISAM for searchindex, MAX_ROWS, etc
 * @todo Handle lengths on indexes, eg: el_from, el_to(40)
 * @toto Handle REFERENCES/ON DELETE CASCADE
 */
abstract class SchemaBuilder {
	// Final SQL to be output
	private $outputSql = '';

	// If at any point we fail, set this to false
	protected $isOk = true;

	// The prefix used for all tables
	protected $tblPrefix = '';

	// Any options for the table creation. Things like ENGINE=InnoDB
	protected $tblOptions = array();

	// Our table definition
	protected $tables = array();

	/**
	 * Constructor. We hide it so people don't try to construct their own schema
	 * classes. Use a sane entry point, like newFromType() or newFromCustomSchema()
	 *
	 * @param $schema Array See Schema::$defaultTables for more information
	 */
	private final function __construct( $schema ) {
		// wfRunHooks( 'LoadExtensionSchemaUpdates', array( &$schema ) );
		$this->tables = $schema;
		$this->addDatabaseSpecificTables();
	}

	/**
	 * Get a brand new Mediawiki schema for a given DB type
	 *
	 * @param $type String A database type (eg: mysql, postgres, sqlite)
	 * @return SchemaBuilder subclass
	 */
	public static function newFromType( $type ) {
		$class = ucfirst( strtolower( $type ) ) . 'Schema';
		if ( !class_exists( $class ) ) {
			throw new Exception( "No such database class $class" );
		} else {
			return new $class( Schema::$defaultTables );
		}
	}

	/**
	 * Top-level create method. Loops the tables and passes them to the child
	 * classes for implementation
	 * 
	 * @return boolean
	 */
	public function createAllTables() {
		foreach( $this->tables as $name => $definition ) {
			$this->outputSql .= $this->createTable( $name, $definition );
		}
		return $this->isOk;
	}

	/**
	 * Similar to generateTables(), but only generates SQL for tables that do not exist
	 *
	 * @param $db Database object
	 * @return boolean
	 */
	public function updateAllTables( DatabaseBase $db ) {
		$this->setTablePrefix( $db->tablePrefix() );
		foreach( $this->tables as $name => $definition ) {
			if( $db->tableExists( $name ) ) {
				$this->outputSql .= $this->updateTable( $name, $definition, $db );
			} else {
				$this->outputSql .= $this->createTable( $name, $definition );
			}
		}
		return $this->isOk;
	}

	/**
	 * Get the final DBMS-specific SQL
	 * 
	 * @return string
	 */
	public function getSql() {
		return $this->outputSql;
	}

	/**
	 * Set the prefix for all tables, usually $wgDBprefix
	 * 
	 * @param $prefix String The prefix to use for all table names
	 */
	public function setTablePrefix( $prefix ) {
		$this->tblPrefix = $prefix;
	}

	/**
	 * Set the default table options for all tables
	 * @param $opts Array of table options, like 'engine' => 'InnoDB', etc
	 */
	public function setTableOptions( $opts ) {
		$this->tblOptions = $opts;
	}

	/**
	 * Given an abstract table definition, return a DBMS-specific command to
	 * create it.
	 * @param $name The name of the table, like 'page' or 'revision'
	 * @param $definition Array An abstract table definition
	 * @return String
	 */
	abstract protected function createTable( $name, $definition );

	/**
	 * Given an abstract table definition, check the current table and see if
	 * it needs updating, returning appropriate update queries as needed.
	 * @param $name The name of the table, like 'page' or 'revision'
	 * @param $definition Array An abstract table definition
	 * @param $db DatabaseBase object, referring to current wiki DB
	 * @return String
	 */
	abstract protected function updateTable( $name, $definition, $db );

	/**
	 * Adds database-specific tables to the in-class list.
	 * @return Nothing
	 */
	abstract protected function addDatabaseSpecificTables();
}

class MysqlSchema extends SchemaBuilder {
	protected function addDatabaseSpecificTables() {
		$this->tables['searchindex'] = array(
			'prefix' => 'si',
			'fields' => array(
				'page' => array(
					'type'   => Schema::TYPE_INT,
					'signed' => false,
					'null'   => false,
				),
				'title' => array(
					'type'    => Schema::TYPE_VARCHAR,
					'length'  => 255,
					'null'    => false,
					'default' => '',
				),
				'text' => array(
					'type'   => Schema::TYPE_TEXT,
					'length' => 'medium',
					'null'   => false,
				),
			),
			'indexes' => array(
				'si_page' => array(
					'UNIQUE', 'page',
				),
				'si_title' => array(
					'FULLTEXT', 'title',
				),
				'si_text' => array(
					'FULLTEXT', 'text',
				),
			),
			'options' => array(
				'engine' => 'MyISAM',
			),
		);

		$this->tables['revision']['options'] = array(
			'max_rows' => 10000000,
			'avg_row_length' => 1024,
		);

		$this->tables['text']['options'] = array(
			'max_rows' => 10000000,
			'avg_row_length' => 10240,
		);

		$this->tables['hitcounter']['options'] = array(
			'max_rows' => 25000,
			'engine' => 'HEAP',
		);
	}

	/**
	 * @see SchemaBuilder::createTable()
	 */
	protected function createTable( $name, $def ) {
		$prefix = $def['prefix'] ? $def['prefix'] . '_' : '';
		$tblName = $this->tblPrefix . $name;
		$opts = isset( $def['options'] ) ? $def['options'] : array();
		$sql = "CREATE TABLE `$tblName` (";
		foreach( $def['fields'] as $field => $attribs ) {
			$sql .= "\n\t{$prefix}{$field} " . $this->getFieldDefinition( $attribs );
		}
		$sql = rtrim( $sql, ',' );
		$sql .= "\n) " . $this->getTableOptions( $opts ) . ";\n";
		if( isset( $def['indexes'] ) ) {
			foreach( $def['indexes'] as $idx => $idxDef ) {
				if( $idxDef[0] === 'UNIQUE' ) {
					array_shift( $idxDef );
					$sql .= "CREATE UNIQUE INDEX ";
				} elseif( $idxDef[0] == 'FULLTEXT' ) {
					array_shift( $idxDef );
					$sql .= "CREATE FULLTEXT INDEX ";
				} else {
					$sql .= "CREATE INDEX ";
				}
				$sql .= "{$prefix}{$idx} ON $tblName (";
				foreach( $idxDef as $col ) {
					$sql .= "{$prefix}{$col},";
				}
				$sql = rtrim( $sql, ',' );
				$sql .= ");\n";
			}
		}
		return $sql . "\n";
	}

	/**
	 * Given an abstract field definition, return a MySQL-specific definition.
	 * @param $attribs Array An abstract table definition
	 * @return String
	 */
	private function getFieldDefinition( $attribs ) {
		if( !isset( $attribs['type'] ) ) {
			$this->isOk = false;
			throw new Exception( "No type specified for field" );
		}
		$fieldType = $attribs['type'];
		$def = '';
		switch( $fieldType ) {
			case Schema::TYPE_INT:
				$def = 'int';
				if( isset( $attribs['length'] ) ) {
					$def = $attribs['length'] . $def;
				}
				break;
			case Schema::TYPE_VARCHAR:
				$def = 'varchar(' . $attribs['length'] . ')';
				break;
			case Schema::TYPE_CHAR:
				$def = 'char(' . $attribs['length'] . ')';
				break;
			case Schema::TYPE_DATETIME:
				$def = 'binary(14)';
				break;
			case Schema::TYPE_TEXT:
				$def = 'text';
				if( isset( $attribs['length'] ) ) {
					$def = $attribs['length'] . $def;
				}
				break;
			case Schema::TYPE_BLOB:
				$def = 'blob';
				if( isset( $attribs['length'] ) ) {
					$def = $attribs['length'] . $def;
				}
				break;
			case Schema::TYPE_BINARY:
				$def = 'binary(' . $attribs['length'] . ')';
				break;
			case Schema::TYPE_VARBINARY:
				$def = 'varbinary(' . $attribs['length'] . ')';
				break;
			case Schema::TYPE_BOOL:
				$def = 'bool';
				break;
			case Schema::TYPE_ENUM:
				$def = 'ENUM("' . implode( '", "', $attribs['values'] );
				$def = rtrim( $def, ', "' ) . '")';
				break;
			case Schema::TYPE_FLOAT:
				$def = 'float';
				break;
			case Schema::TYPE_REAL:
				$def = 'real';
				break;
			default:
				$this->isOk = false;
		}
		if( isset( $attribs['signed'] ) ) {
			$def .= $attribs['signed'] ? ' signed' : ' unsigned';
		}
		if( isset( $attribs['binary'] ) && $attribs['binary'] ) {
			$def = $def . ' binary';
		}
		if( isset( $attribs['null'] ) ) {
				$def .= $attribs['null'] ? ' NULL' : ' NOT NULL';
		}
		// Use array_key_exists() since 'default' might be set to null
		if( array_key_exists( 'default', $attribs ) ) {
			if( $attribs['default'] === null ) {
				$def .= ' default NULL';
			} else {
				$def .= " default '" . $attribs['default'] . "'";
			}
		}
		if( isset( $attribs['primary-key'] ) && $attribs['primary-key'] ) {
			$def .= " PRIMARY KEY";
		}
		if( isset( $attribs['auto-increment'] ) && $attribs['auto-increment'] ) {
			$def .= " AUTO_INCREMENT";
		}
		return $def . ",";
	}

	private function getTableOptions( $opts ) {
		$opts = array_merge( $this->tblOptions, $opts );
		$ret = array();
		foreach( $opts as $name => $value ) {
			$ret[] = strtoupper( $name ) . "=$value";
		}
		return implode( ', ', $ret );
	}

	protected function updateTable( $name, $definition, $db ) {
		return '';
	}
}

class SqliteSchema extends SchemaBuilder {
	static $typeMapping = array(
		Schema::TYPE_INT       => 'INTEGER',
		Schema::TYPE_VARCHAR   => 'TEXT',
		Schema::TYPE_DATETIME  => 'TEXT',
		Schema::TYPE_TEXT      => 'TEXT',
		Schema::TYPE_BLOB      => 'BLOB',
		Schema::TYPE_BINARY    => 'BLOB',
		Schema::TYPE_VARBINARY => 'BLOB',
		Schema::TYPE_BOOL      => 'INTEGER',
		Schema::TYPE_ENUM      => 'BLOB',
		Schema::TYPE_FLOAT     => 'REAL',
		Schema::TYPE_REAL      => 'REAL',
		Schema::TYPE_CHAR      => 'TEXT',
		Schema::TYPE_NONE      => '',
	);
	
	/**
	 * @todo: update updatelog with fts3
	 */
	protected function addDatabaseSpecificTables() {
		$tmpFile = tempnam( sys_get_temp_dir(), 'mw' );
		$db = new DatabaseSqliteStandalone( $tmpFile );
		if ( $db->getFulltextSearchModule() == 'FTS3' ) {
			$this->tables['searchindex'] = array(
				'prefix' => 'si',
				'virtual' => 'FTS3',
				'fields' => array(
					'title' => array(
						'type' => Schema::TYPE_NONE,
					),
					'text' => array(
						'type' => Schema::TYPE_NONE,
					),
				)
			);
		} else {
			$this->tables['searchindex'] = array(
				'prefix' => 'si',
				'fields' => array(
					'title' => array(
						'type' => Schema::TYPE_TEXT,
					),
					'text' => array(
						'type' => Schema::TYPE_TEXT,
					),
				)
			);
		}
		$db->close();
		unlink( $tmpFile );
	}

	protected function createTable( $name, $def ) {
		$prefix = $def['prefix'] ? $def['prefix'] . '_' : '';
		$tblName = $this->tblPrefix . $name;
		$virtual = isset ( $def['virtual'] ) ? $def['virtual'] : false;
		if ( $virtual ) {
			$sql = "CREATE VIRTUAL TABLE `$tblName` USING $virtual (";
		} else {
			$sql = "CREATE TABLE `$tblName` (";
		}
		foreach( $def['fields'] as $field => $attribs ) {
			$sql .= "\n\t{$prefix}{$field} " . $this->getFieldDefinition( $attribs );
		}
		$sql = rtrim( $sql, ',' );
		$sql .= "\n);\n";
		if( isset( $def['indexes'] ) ) {
			foreach( $def['indexes'] as $idx => $idxDef ) {
				if( $idxDef[0] === 'UNIQUE' ) {
					array_shift( $idxDef );
					$sql .= "CREATE UNIQUE INDEX ";
				} elseif( $idxDef[0] == 'FULLTEXT' ) {
					continue; // no thanks
				} else {
					$sql .= "CREATE INDEX ";
				}
				$sql .= "{$prefix}{$idx} ON $tblName (";
				foreach( $idxDef as $col ) {
					$sql .= "{$prefix}{$col},";
				}
				$sql = rtrim( $sql, ',' );
				$sql .= ");\n";
			}
		}
		return $sql . "\n";
	}

	/**
	 * Given an abstract field definition, return a MySQL-specific definition.
	 * @param $attribs Array An abstract table definition
	 * @return String
	 */
	private function getFieldDefinition( $attribs ) {
		$type = $attribs['type'];
		if ( !isset( self::$typeMapping[$type] ) ) {
			throw new MWException( "Unknown type $type" );
		}
		$def = self::$typeMapping[$type];
		if( isset( $attribs['null'] ) ) {
				$def .= $attribs['null'] ? ' NULL' : ' NOT NULL';
		}
		// Use array_key_exists() since 'default' might be set to null
		if( array_key_exists( 'default', $attribs ) ) {
			if( $attribs['default'] === null ) {
				$def .= ' default NULL';
			} else {
				$def .= " DEFAULT '" . $attribs['default'] . "'";
			}
		}		if( isset( $attribs['primary-key'] ) && $attribs['primary-key'] ) {
			$def .= ' PRIMARY KEY';
		}
		if( isset( $attribs['auto-increment'] ) && $attribs['auto-increment'] ) {
			$def .= ' AUTOINCREMENT';
		}
		return $def . ',';
	}

	protected function updateTable( $name, $definition, $db ) {
		return '';
	}
}