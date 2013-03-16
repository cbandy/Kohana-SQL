<?php
namespace SQL;

/**
 * @package SQL
 * @author  Chris Bandy
 *
 * @covers  SQL\Transaction_Manager::__construct
 */
class Transaction_ManagerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @covers  SQL\Transaction_Manager::commit
	 * @covers  SQL\Transaction_Manager::reset
	 */
	public function test_commit_named_transaction()
	{
		$connection = $this->getMockForAbstractClass('SQL\Transactions');
		$manager = new Transaction_Manager($connection);
		$name = $manager->start();

		$connection->expects($this->once())->method('commit');

		$this->assertNull($manager->commit($name));
	}

	/**
	 * @covers  SQL\Transaction_Manager::commit
	 */
	public function test_commit_savepoint()
	{
		$connection = $this->getMockForAbstractClass('SQL\Savepoints');
		$manager = new Transaction_Manager($connection);
		$manager->start();
		$name = $manager->start();

		$connection->expects($this->once())->method('release')
			->with($this->identicalTo($name));

		$this->assertNull($manager->commit($name));
	}

	/**
	 * @covers  SQL\Transaction_Manager::commit
	 * @covers  SQL\Transaction_Manager::reset
	 */
	public function test_commit_transaction()
	{
		$connection = $this->getMockForAbstractClass('SQL\Transactions');
		$manager = new Transaction_Manager($connection);

		$connection->expects($this->once())->method('commit');

		$this->assertNull($manager->commit());
	}

	/**
	 * @covers  SQL\Transaction_Manager::rollback
	 * @covers  SQL\Transaction_Manager::reset
	 */
	public function test_rollback_named_transaction()
	{
		$connection = $this->getMockForAbstractClass('SQL\Transactions');
		$manager = new Transaction_Manager($connection);
		$name = $manager->start();

		$connection->expects($this->once())->method('rollback');

		$this->assertNull($manager->rollback($name));
	}

	/**
	 * @covers  SQL\Transaction_Manager::rollback
	 */
	public function test_rollback_savepoint()
	{
		$connection = $this->getMockForAbstractClass('SQL\Savepoints');
		$manager = new Transaction_Manager($connection);
		$manager->start();
		$name = $manager->start();

		$connection->expects($this->once())->method('rollback_to')
			->with($this->identicalTo($name));
		$connection->expects($this->once())->method('release')
			->with($this->identicalTo($name));

		$this->assertNull($manager->rollback($name));
	}

	/**
	 * @covers  SQL\Transaction_Manager::rollback
	 * @covers  SQL\Transaction_Manager::reset
	 */
	public function test_rollback_transaction()
	{
		$connection = $this->getMockForAbstractClass('SQL\Transactions');
		$manager = new Transaction_Manager($connection);

		$connection->expects($this->once())->method('rollback');

		$this->assertNull($manager->rollback());
	}

	/**
	 * @covers  SQL\Transaction_Manager::start
	 * @covers  SQL\Transaction_Manager::generate_name
	 */
	public function test_start_generates_a_unique_name()
	{
		$connection = $this->getMockForAbstractClass('SQL\Savepoints');
		$connection->expects($this->once())->method('start');
		$connection->expects($this->atLeastOnce())->method('savepoint');
		$manager = new Transaction_Manager($connection);
		$previous_names = array();

		for ($i = 0, $max = rand(2, 10); $i < $max; ++$i)
		{
			$name = $manager->start();
			$this->assertInternalType('string', $name);
			$this->assertNotEmpty($name);
			$this->assertNotContains($name, $previous_names);
			$previous_names[] = $name;
		}
	}

	/**
	 * @covers  SQL\Transaction_Manager::start
	 */
	public function test_start_savepoint()
	{
		$connection = $this->getMockForAbstractClass('SQL\Savepoints');
		$manager = new Transaction_Manager($connection);
		$manager->start();
		$name = 'kohana_txn_2';

		$connection->expects($this->once())->method('savepoint')
			->with($this->identicalTo($name));

		$this->assertSame($name, $manager->start());
	}

	/**
	 * @covers  SQL\Transaction_Manager::start
	 */
	public function test_start_transaction()
	{
		$connection = $this->getMockForAbstractClass('SQL\Transactions');
		$manager = new Transaction_Manager($connection);

		$connection->expects($this->once())->method('start');

		$manager->start();
	}
}
