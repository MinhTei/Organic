<?php
class Book extends Db
{
	public function getRand($n)
	{
		$sql = "select book_id, book_name, img from book order by rand() limit 0, $n ";
		return $this->select($sql);
	}

	public function getByPubliser($manhaxb, $n = 5)
	{
		$limit = intval($n);
		$sql = "select book_id, book_name, img from book where pub_id = ? order by rand() limit 0, $limit ";
		return $this->select($sql, array($manhaxb));
	}

	// Lấy tất cả sách
	public function getAll()
	{
		$sql = "select * from book order by book_id";
		return $this->select($sql);
	}

	// Thêm sách
	public function addBook($data)
	{
		$sql = "INSERT INTO book (book_id, book_name, description, price, img, pub_id, cat_id)\n                VALUES (:book_id, :book_name, :description, :price, :img, :pub_id, :cat_id)";
		return $this->insert($sql, $data);
	}

	// Xóa sách theo id
	public function deleteBook($book_id)
	{
		$sql = "DELETE FROM book WHERE book_id = :book_id";
		return $this->delete($sql, array(':book_id' => $book_id));
	}
}
