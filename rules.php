<?php
    session_start();

    include('functions.php');

    //connect to database
    $con = connect_to_database();
?>

<!DOCTYPE html>
<html>
    <head>
        <title>4kev.org</title>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
        <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
        <link rel="stylesheet" type="text/css" href="/style.css?v=<?=time();?>">
        <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="../../myjs.js?v=<?=time();?>" type="text/javascript"></script>
        <script src="jquery-3.2.0.min.js"></script>
    </head>
    <body>
        <div class="bgImage">
            <?php boardList(); ?>

            <!--BANNER-->
            <center>
                <?php
                    $banner = "<a href='http://4kev.org/'><img src='/banners/" . rand(0, 38) . ".gif' /></a>";
                    echo $banner;
                ?>

                <br><br>
                <table>
                    <tr>
                        <td>
                            <center>
                                <p style="font-size:30px;"><b>There are no rules</b></p>
                                <?php echo "<p>If we don't like what you post, you get banned</p>"; ?>
                            </center>
                        <td>
                    </tr>
                </table>
            <br>
            <hr>
        </div>
    </body>
</html>
