<?php
require_once __DIR__ . '/Db.php';

class Book extends Db
{
    public function __construct($host, $db, $user, $pass)
    {
        parent::__construct($host, $db, $user, $pass);
    }

    public function getAll()
    {
        return $this->select('SELECT * FROM book ORDER BY book_id');
    }

    public function getPublishers()
    {
        return $this->select('SELECT * FROM publisher');
    }

    public function getCategories()
    {
        return $this->select('SELECT * FROM category');
    }

    public function addBook($data)
    {
        $sql = "INSERT INTO book (book_id, book_name, description, price, img, pub_id, cat_id)\n                VALUES (:book_id, :book_name, :description, :price, :img, :pub_id, :cat_id)";
        return $this->insert($sql, $data);
    }

    public function deleteBook($book_id)
    {
        return $this->delete('DELETE FROM book WHERE book_id = :book_id', array(':book_id' => $book_id));
    }

    // Lấy sách với phân trang
    public function getBooksPaging($page, $limit)
    {
        $offset = ($page - 1) * $limit;
        $limit = (int)$limit;
        $offset = (int)$offset;
        $sql = "SELECT * FROM book ORDER BY book_id LIMIT $offset, $limit";
        return $this->select($sql);
    }

    // Đếm tổng số sách
    public function countAll()
    {
        $sql = "SELECT COUNT(*) as total FROM book";
        $result = $this->select($sql);
        return isset($result[0]['total']) ? $result[0]['total'] : 0;
    }

    // Lấy thông tin sách theo id
    public function getBook($book_id)
    {
        $sql = 'SELECT * FROM book WHERE book_id = :book_id';
        $res = $this->select($sql, array(':book_id' => $book_id));
        return isset($res[0]) ? $res[0] : null;
    }

    // Cập nhật thông tin sách
    public function updateBook($book_id, $data)
    {
        $sql = "UPDATE book SET book_name = :book_name, description = :description, price = :price, img = :img, pub_id = :pub_id, cat_id = :cat_id WHERE book_id = :book_id";
        // Ensure book_id placeholder is present
        $data[':book_id'] = $book_id;
        return $this->update($sql, $data);
    }
}
