<?php
require_once("config.php");
function getBookDetails($db){
$rec_limit = 25;
$whereArr = array();
if(isset($_GET{'gutenberg_id'} ) && $_GET{'gutenberg_id'} != "") {
	$gutenberg_id = explode(',',trim(strtolower($_GET{'gutenberg_id'})));
	if($gutenberg_id != "") $whereArr[] = "LOWER(b.gutenberg_id) in ('" . implode("','", $gutenberg_id) . "')";
}
if(isset($_GET{'language'}) && $_GET{'language'} != "") {
$language = explode(',',strtolower(trim($_GET{'language'})));
if($language != "") $whereArr[] = "LOWER(bl.code) in ('" . implode("','", $language) . "')";
}
if(isset($_GET{'mimetype'} ) && $_GET{'mimetype'} != "") {
$mimetype = explode(',',strtolower($_GET{'mimetype'}));
$mimetype = implode("|", $mimetype);
if($mimetype != "") $whereArr[] = "LOWER(bf.mime_type) REGEXP '" . $mimetype . "'";
}
if(isset($_GET{'topic'} ) && $_GET{'topic'} != "") {
$topic = explode(',',strtolower(trim($_GET{'topic'})));
$topic = implode("|", $topic);
if($topic != "") $whereArr[] = "LOWER(bs.name) REGEXP '" . $topic . "' AND LOWER(bb.name) REGEXP '" . $topic . "'";
}
if(isset($_GET{'author'}) && $_GET{'author'} != "") {
$author = explode(',',trim(strtolower($_GET{'author'})));
$author = trim(implode("|", $author));
if($author != "") $whereArr[] = "LOWER(au.name) REGEXP '" . $author . "'";
}

if(isset($_GET{'title'}) && $_GET{'title'} != "") {
$title = explode(',',trim(strtolower($_GET{'title'})));
$title = trim(implode("|", $title));
if($title != "") $whereArr[] = "LOWER(b.title) REGEXP '" . $title . "'";
}


$whereStr = implode(" AND ", $whereArr);

if($whereStr !=""){
$getDetails_cnt = "SELECT Count(distinct b.id) as number_of_book from books_book b left join books_book_authors ba ON b.id = ba.book_id left join books_author au on au.id = ba.author_id left join books_book_languages lan on b.id = lan.book_id left join books_language bl on bl.id = lan.language_id left join books_book_bookshelves bbb on b.id = bbb.book_id left join books_bookshelf bb on bbb.bookshelf_id = bb.id left join books_book_subjects bbs on bbs.book_id = b.id left join books_subject bs on bs.id = bbs.subject_id left join books_format bf on bf.book_id =b.id where $whereStr  order by b.download_count desc";
}else{
	$getDetails_cnt = "SELECT Count(distinct b.id) as number_of_book from books_book b left join books_book_authors ba ON b.id = ba.book_id left join books_author au on au.id = ba.author_id left join books_book_languages lan on b.id = lan.book_id left join books_language bl on bl.id = lan.language_id left join books_book_bookshelves bbb on b.id = bbb.book_id left join books_bookshelf bb on bbb.bookshelf_id = bb.id left join books_book_subjects bbs on bbs.book_id = b.id left join books_subject bs on bs.id = bbs.subject_id left join books_format bf on bf.book_id =b.id order by b.download_count desc";
}
$fetch_books_cnt = mysqli_query($db, $getDetails_cnt) or die(mysqli_error($db));
$book_cnt = mysqli_fetch_array($fetch_books_cnt);
$num_book_cnt = $book_cnt[0];
if($num_book_cnt > 0){         
 if(isset($_GET{'page'} ) && $_GET{'page'} != "" && $_GET{'page'} >1) {
	//$page = $_GET{'page'} + 1;
	$page = $_GET{'page'}-1;
	$offset = $rec_limit * $page ;
 }else {
	$page = 0;
	$offset = 0;
 }
 
 $left_rec = $num_book_cnt - ($page * $rec_limit);
if($whereStr !=""){
$getDetails = "SELECT b.id,b.download_count, b.title,b.media_type,au.name,au.birth_year,au.death_year, bl.code as language  from books_book b left join books_book_authors ba ON b.id = ba.book_id left join books_author au on au.id = ba.author_id left join books_book_languages lan on b.id = lan.book_id left join books_language bl on bl.id = lan.language_id left join books_book_bookshelves bbb on b.id = bbb.book_id left join books_bookshelf bb on bbb.bookshelf_id = bb.id left join books_book_subjects bbs on bbs.book_id = b.id left join books_subject bs on bs.id = bbs.subject_id left join books_format bf on bf.book_id =b.id where $whereStr  group by b.id order by b.download_count desc limit $offset, $rec_limit";
}else{
$getDetails = "SELECT b.id,b.download_count, b.title,b.media_type,au.name,au.birth_year,au.death_year, bl.code as language  from books_book b left join books_book_authors ba ON b.id = ba.book_id left join books_author au on au.id = ba.author_id left join books_book_languages lan on b.id = lan.book_id left join books_language bl on bl.id = lan.language_id left join books_book_bookshelves bbb on b.id = bbb.book_id left join books_bookshelf bb on bbb.bookshelf_id = bb.id left join books_book_subjects bbs on bbs.book_id = b.id left join books_subject bs on bs.id = bbs.subject_id left join books_format bf on bf.book_id =b.id  group by b.id order by b.download_count desc limit $offset, $rec_limit";	
}

$List_array =array();
$result_arr['count'] = $num_book_cnt;
$result_arr['results'] =array();
$bookList_array =array();
$author_array = array();
$bookSubject_array =array();
$bookShelf_array =array();
$mime_type_array =array();
$language_array = array();
$error_array= array();

$fetch_books = mysqli_query($db, $getDetails) or die(mysqli_error($db));
while($rows=mysqli_fetch_assoc($fetch_books)){
	 $row = array_map('utf8_encode', $rows);
	 $bookList_array['book_id'] = $row['id'];
	 $bookList_array['author'] = array();
	 $author_array['birth_year'] = $row['birth_year'];
	 $author_array['death_year'] = $row['death_year'];
	 $author_array['name'] = $row['name'];
	 array_push($bookList_array['author'],$author_array);
	 $bookList_array['bookshelves'] = array();
	 $getBookshelf = "SELECT bb.name as Bookshelf  FROM books_book_bookshelves bbb join books_bookshelf bb on bbb.bookshelf_id = bb.id where bbb.book_id = ".$row['id']."";
	 $fetch_Bookshelfs = mysqli_query($db, $getBookshelf) or die(mysqli_error($db));
	 while ($row_Bookshelfs = mysqli_fetch_assoc($fetch_Bookshelfs)) {
		 $row_Bookshelf = array_map('utf8_encode', $row_Bookshelfs);
		 
		array_push($bookList_array['bookshelves'],$row_Bookshelf['Bookshelf']);
	 }
	 $bookList_array['download_count'] = $row['download_count'];
	 $getBookmime = "SELECT mime_type,url from books_format where book_id = ".$row['id']."";
	 $fetch_Bookmimes = mysqli_query($db, $getBookmime) or die(mysqli_error($db));
	 while ($row_Bookmimes = mysqli_fetch_assoc($fetch_Bookmimes)) {
		 $row_BookMime = array_map('utf8_encode', $row_Bookmimes);
		 $mime_type_array[$row_BookMime['mime_type']] = $row_BookMime['url'];
		
	 }
	 $array_format = json_decode(json_encode ($mime_type_array), FALSE);
	 $bookList_array['formats'] = $array_format;
	 $bookList_array['languages'] = array();
	 array_push($bookList_array['languages'],$row['language']);
	 $bookList_array['media_type'] = $row['media_type'];
	 $bookList_array['Subjects'] = array();
	 $getSubjects = "select bs.name as subject,bs.id as sub_id from books_subject bs join books_book_subjects bbs on bs.id = bbs.subject_id where bbs.book_id =".$row['id']."";
	 $fetch_subjects = mysqli_query($db, $getSubjects) or die(mysqli_error($db));
	 while ($row_subjects = mysqli_fetch_assoc($fetch_subjects)) {
		 $row_subject = array_map('utf8_encode', $row_subjects);
		 array_push($bookList_array['Subjects'],$row_subject['subject']);
		
	 }
	 $bookList_array['title'] = $row['title'];
	 
	 
	 array_push($result_arr['results'],$bookList_array);
 }
 if(count($result_arr['results'])){ 
  $jsonData = json_encode($result_arr,JSON_UNESCAPED_SLASHES);
 }else{
	$error_array['Details'] ="Invalid page.";
	 $jsonData = json_encode($error_array);
 }
  
}else{
	 $error_array['Details'] ="No Records found.";
	 $jsonData = json_encode($error_array);
 }
 return $jsonData;
}
	
echo getBookDetails($db);
?>