<?php
    require 'db.php'; //db connection

    function CBR_XML_Daily_Ru() { //API for currency
        static $rates;
        if ($rates === null) {
            $rates = json_decode(file_get_contents('https://www.cbr-xml-daily.ru/daily_json.js'));
        }
        return $rates;
    }
    $data = CBR_XML_Daily_Ru();

    //sorting by prices and authors
    $get_mode = $_GET['order_mode'];

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Books</title>
    <meta name="description" content="">

    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="dns-prefetch" href="//maps.googleapis.com">
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway:400,500,700,800&display=swap">
    <link rel="stylesheet" href="css/main-76b66b5da2.css">
</head>
<body id="pdf">
    <div class="wrap">
        <nav class="menu">
            <span class="menu__label">
                Сортировка:
            </span>
            <ul class="menu__list">
                <li class="check">
                    <a href="?order_mode=price_up" class="link menu__link">
                        По цене ▲
                    </a>
                </li>
                <li class="check">
                    <a href="?order_mode=price_down" class="link menu__link">
                        По цене ▼
                    </a>
                </li>
                <li class="check">
                    <a href="?order_mode=author_up" class="link menu__link">
                        По автору ▲
                    </a>
                </li>
                <li class="check">
                    <a href="?order_mode=author_down" class="link menu__link">
                        По автору ▼
                    </a>
                </li>
            </ul>
        </nav>
        
    <h1 class="v-hidden">
        Книги
    </h1>
    <div class="items">
        <?php
            if($get_mode == "price_up") $getTableBooks = R::findCollection('books', "ORDER BY price ASC");
            elseif ($get_mode == "price_down") $getTableBooks = R::findCollection('books', "ORDER BY price DESC");
            else $getTableBooks = R::findCollection('books');
            if($get_mode == "author_up" || $get_mode == "author_down") {
                if($get_mode == "author_up") {
                    $getTableAuthors = R::findCollection("authors", "ORDER BY name ASC");
                }
                if($get_mode == "author_down") {
                    $getTableAuthors = R::findCollection("authors", "ORDER BY name DESC");
                }
                $a = 0;
                while ($getTableAuthor = $getTableAuthors->next()){
                    $AuthorArray[$a] = $getTableAuthor->id; //fill in array @ids@ data  
                    $a++; //increment for iteration
                }
            }
            $k = 0;
            $loadBooks = R::loadAll("books", $AuthorArray);
            while ($gotTableBooks = $getTableBooks->next()){
                $author_id = $gotTableBooks->author_id;
                $genre_id = $gotTableBooks->genre_id;
                $loadGenre = R::load("genres", $loadBooks[$AuthorArray[$k]]->genre_id); //Getting table @genres@ and loading sorted @genre@ identificators  
                $loadAuthor = R::load("authors", $loadBooks[$AuthorArray[$k]]->author_id); //Getting table @authors@ and loading sorted @author@ identificators  
                $timestamp = strtotime($gotTableBooks->publication_date); // Getting publication date and converting this to time
                $year = date('Y', $timestamp); //taking only @year@ from publication date
                if(mb_strlen($gotTableBooks->description) > 200){
                    $text = trim(substr($gotTableBooks->description,0, strripos(substr($gotTableBooks->description,0,370),' ')), "\,"); // cutting strings > 200 symbols and deleting @,@ in end of each sentence
                }
                else $text = $gotTableBooks->description; //if text < then 200 symbols, just show
                $avtor = R::load('authors', $author_id);
                $genres = R::load('genres', $genre_id);
                if($get_mode == "author_up" || $get_mode == "author_down"){ //Sorting for author_down and author_up 
                    $gotTableBooks->name = $loadBooks[$AuthorArray[$k]]->name;
                    $avtor->name = $loadAuthor->name;
                    $genres->name = $loadGenre->name;
                    $ts = strtotime($loadBooks[$AuthorArray[$k]]->publication_date);
                    $year = date('Y', $ts);
                    $gotTableBooks->image = $loadBooks[$AuthorArray[$k]]->image;
                    if(strlen($loadBooks[$AuthorArray[$k]]->description) > 200) $text = trim(substr($loadBooks[$AuthorArray[$k]]->description, 0, strripos(substr($loadBooks[$AuthorArray[$k]]->description, 0 , 370), ' ')), "\,");
                    else $loadBooks[$AuthorArray[$k]]->description;
                    $gotTableBooks->price = $loadBooks[$AuthorArray[$k]]->price;
                    }
                ?>
                    <article class="entry">
                        <header class="entry__header">
                            <h2 class="heading-2 entry__title">
                                <?php echo $gotTableBooks->name; ?>
                            </h2>
                            <div class="entry__meta">
                                <span>
                                    <?php echo $avtor->name; ?>
                                </span>
                                <span>
                                    <?php echo $year; ?>
                                </span>
                                <span>
                                    <?php echo $genres->name; ?>
                                </span>
                            </div>
                        </header>
                        <div class="entry__main">
                            <div class="entry__image">
                                <img src="img/books/<?php echo $gotTableBooks->image; ?>" alt="<?php echo $gotTableBooks->name; ?>">
                            </div>
                            <div>
                                <div class="entry__desc">
                                    <p>
                                        <?php echo $text . '...'; ?>
                                    </p>
                                </div>
                                <a href="#" class="link">
                                    Полное описание
                                </a>
                                <div class="entry__bar">
                                    <div class="entry__price">
                                        <span>
                                            <?php echo intval(round($gotTableBooks->price)) . ' ₽'; ?>
                                        </span>
                                        <span>
                                            <?php echo intval(round($gotTableBooks->price/$data->Valute->EUR->Value)) . ' €'; ?>
                                        </span>
                                    </div>
                                    <button type="button" class="button button_dark">
                                        Купить
                                    </button>
                                </div>
                            </div>
                        </div>
                    </article>
        <?php
                //Min. && Max. year
                $dateArray[$k] = [intval($year)]; //fill in data from DB 
                $tempArray1[$k] = $dateArray[$k][0]; //refill in years to array
                //Average price
                $array[$k] = [intval(round($gotTableBooks->price))]; //rounding prices and converting to integer
                $res += array_sum($array[$k]); //finding sum
                    //top3 places of max price
                    $tempArray2[$k] = $array[$k][0]; 
                    $tempArray3[$k] = $array[$k][0];
                    rsort($tempArray2);
                    $tempArray2 = array_slice($tempArray2, 0, 3); //cutting first 3 index from array
                $k++; //increment
            }
            $min = min($tempArray1);
            $max = max($tempArray1);
            if($get_mode == NULL) { //this constraction neccessery, if order_mode is empty
                for ($z = 0; $z < 3; $z++) {
                    $sArray[$z] = array_search($tempArray2[$z], $tempArray3);
                    $sArray[$z] += 1;
                }
                $load = R::loadAll("books", $sArray);
            }
            else { //if order_mode isn't `NULL`
                $reloadCollections = R::findCollection("books");
                $i = 0;
                while ($reloadCollection = $reloadCollections->next()){
                    $priceArray[$i] = [intval(round($reloadCollection->price))];
                    $tempArray4[$i] = $priceArray[$i][0];
                    $i++;
                }
                for ($z = 0; $z < 3; $z++) {
                    $sArray[$z] = array_search($tempArray2[$z], $tempArray4);
                    $sArray[$z] += 1;
                }
                $load = R::loadAll("books", $sArray);
            }
        ?>
        
    </div>

        <dl class="meta">
            <div class="meta__item">
                <dt>
                    Всего книг:
                </dt>
                <dd>
                    <?php echo $count = R::count("books"); ?>
                </dd>
            </div>
            <div class="meta__item">
                <dt>
                    Средняя стоимость:
                </dt>
                <dd>
                    <?php echo $res/$count; ?> ₽
                </dd>
            </div>
            <div class="meta__item">
                <dt>
                    Публикации:
                </dt>
                <dd>
                    <?php echo $min . '-' . $max; ?>
                </dd>
            </div>
        </dl>
        <div class="footer">
            Три самые дорогие книги:<br>
            <?php
                echo $load[$sArray[0]]->name . ' - ' . round($load[$sArray[0]]->price) . ' ₽<br>';
                echo $load[$sArray[1]]->name . ' - ' . round($load[$sArray[1]]->price) . ' ₽<br>';
                echo $load[$sArray[2]]->name . ' - ' . round($load[$sArray[2]]->price) . ' ₽';
            ?>
        </div>
        <form action="index.php" method="post">
            <button type="submit" name="print" class="button" >Создать PDF</button>
        </form>
        <?php
            if(isset($_POST['print'])){
                header("Location: pdfCreator.php");
            }
        ?>
    </div>
    <script
            src="https://code.jquery.com/jquery-3.5.1.js"
            integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc="
            crossorigin="anonymous"></script>
    <script> //this script for sorting button, he's needed for add classes to link @is-active@
        (function($){

            var $curURL = document.location.href;
            $('.check').find('a').each(function() {
                var $linkHref = $(this).attr('href');
                if ($curURL.indexOf($linkHref) > -1) {
                    $(this).addClass('is-active');
                }
            });
        })(jQuery);
    </script>
</body>
</html>
