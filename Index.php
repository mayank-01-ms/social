<?php

session_start();

//Require database connection
require "files/dbconfig.php";
$con = new DBConfig();

//Require class user to get user details
require "files/user.php";
$user = new User();

//Require classs to get post data
require "files/posts.php";
$post = new Posts();

//Including categories files
include "files/categories.php";
$categoryObj = new Categories();

//Require the functions page
require "includes/functions.php";

//Include meta information
include "files/meta.php";

//Checking for session
$loggedIn = isset($_SESSION['loggedIn']) ? $_SESSION['loggedIn'] : false;

$pid = isset($_GET['pid']) ? $_GET['pid'] : false;

$postTitle = isset($_GET['title']) ? $_GET['title'] : false;

$found = 0;

if ($pid == false && $postTitle == false){
    header("Location: index.php");
    exit();
} elseif ($postTitle != false){
    $postTitle = $con->conn->real_escape_string($postTitle);

    $sql = 'SELECT pid, slug FROM posts WHERE slug = "'.$postTitle.'" LIMIT 1;';

    $result = $con->conn->query($sql);

    if ($result->num_rows > 0){
        $row = $result->fetch_assoc();
        $pid = $row['pid'];
        $found = 1;
    }
    else{
        $found = 0;
    }
} 

if ($pid != false){

    //Creating empty variable set 
    $found = 0;
    $title = "Post not found";
    $banner = 'default.png';
    $content = $metaInfo['description'];

    if (!filter_var($pid, FILTER_VALIDATE_INT)){
        echo '<html>
            <head>
                <title>404 Not Found</title>
                <style>
                    *{
                        margin: 0;
                        padding: 0;
                    }
                </style>                
            <link rel="icon" href="'.$metaInfo ['domain'].'/images/logo.png" type="image/png">
            </head>
            <body>';
    echo '<iframe src="'.$metaInfo['domain'].'/error.php" frameBorder="0" width="100%" height="100%"></iframe>'  ;
    echo '</body></html>';
    exit();
    } else {

        //Fetching posts data
        $postData = $post->getPostsInfo($pid);

        if ($postData !== -1){

            $found = 1;
            //Setting up variables
            $title = $postData['title'];
            $date = $postData['date'];
            $auid = $postData['uid'];


            //Get category name
            $category = $categoryObj->categoryName($postData['cat']); 
            
            $banner = $postData['banner'];
            $content = $postData['content'];
            $views = numberShort($postData['views']);
            $available = $postData['available'];
            $numberOfComments = $post->noComments($pid);
            $allowCom = $postData['allow-com'];
            $postTitle = $postData['slug'];

            //Author Details
            $authorName = $user->userName($auid);
            $userLevel = $user->userLevel($auid);
            $userGroup = $user->userGroup($userLevel);
            $profileImage = $user->profileImage($auid);
            $socialLinks = $user->getUserDetails($auid);
            
            $igLink = $socialLinks['ig'] != '0' ? $socialLinks['ig'] : 'javascript:user()';
            $twLink = $socialLinks['tw'] != '0' ? $socialLinks['tw'] : 'javascript:user()';
            $fbLink = $socialLinks['fb'] != '0' ? $socialLinks['fb'] : 'javascript:user()';
            $lnLink = $socialLinks['ln'] != '0' ? $socialLinks['ln'] : 'javascript:user()';

            //Sharing links
            $domainName = $metaInfo['domain'];
            $wa = "whatsapp://send?text=".$domainName."/view-post/".$postTitle;
            $fb = "";
            $tg = "https://t.me/share/url?url=".$domainName."/view-post/".$postTitle."&text=".$title;
            $tw = "https://twitter.com/share?text=".$title."&url=".$domainName."/view-post/".$postTitle."&hastags=".$category;

            //Checking whether a user can comment or not
            if ($loggedIn == true){
                $userlvl = $user->userLevel($_SESSION['uid']);
                $permission = $user->userPermissions($userlvl);
                $canComment = $permission['comment'];
            }

            //Increase view by one
            $post->updateView($pid);

            if ($available != 1){
                $title = 'Deleted post';
            }
        }
        
    }

}
if ($found == 0){
    echo '<html>
            <head>
                <title>404 Not Found</title>
                <style>
                    *{
                        margin: 0;
                        padding: 0;
                    }
                </style>                
            <link rel="icon" href="'.$metaInfo ['domain'].'/images/logo.png" type="image/png">
            </head>
            <body>';
    echo '<iframe src="'.$metaInfo['domain'].'/error.php" frameBorder="0" width="100%" height="100%"></iframe>'  ;
    echo '</body></html>';
    exit();
}

//Page structure 
?>
<!DOCTYPE html>
<html lang="en" data-theme='light'>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#333">

    <meta name="keywords" content="<?php echo $title;?>, <?php echo $metaInfo['keywords'];?>, articles, technology, smartphones, news, review">
    <meta name="description" content="<?php echo strip_tags(substr($content, 0, 150)); ?> ">

    <meta property="og:title" content="<?php echo $title.' - '.$metaInfo['keywords']; ?> " />
    <meta property="og:description" content="<?php echo strip_tags(substr($content, 0, 150)); ?> " />
    <meta property="og:image" content="<?php echo $metaInfo['domain'];?>/banners/<?php echo $banner; ?> " />
    <meta property="og:url" content="<?php echo $metaInfo['ogUrl']?> " />

    <link rel="stylesheet" href="<?php echo $metaInfo ['domain']; ?>/css/styles.css">
    <link rel="stylesheet" href="<?php echo $metaInfo ['domain']; ?>/css/dark.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="icon" href="<?php echo $metaInfo ['domain']; ?>/images/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-172981234-1"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
    
      gtag('config', 'UA-172981234-1');
    </script>
    
    <style>
        #main-doc{
            margin: 0px auto;
            margin-top: 80px;
            width: 1210px;
            display: grid;
            grid-template-rows: 1fr;
            grid-template-columns: 810px 300px;
            grid-column-gap: 100px;
        }
        .left, .right{
            margin-top: 40px;
        }
        .comments{
            display: none;
        }
        @media only screen and (max-width: 600px){
            #main-doc{
                width: 100%;
                display: block;
                margin-top: 50px;
            }
            .right .top:nth-child(1){
                display: none;
            }
            .left, .right{
                margin-top: 10px;
            }
            .comments{
                width: 100%;
            }
            .right .news{
                padding: 0 2.5%;
            }
        }
    </style>
    <title><?php echo $title ." - ". $metaInfo['keywords']?></title>
</head>
<body>
    <header>
        <?php
        include "includes/header.php";
        ?>
    </header>

    <main>
        <div id="main-doc">
            <div class="left">
                <article>
                    <div class="banner">
                        <img src="<?php echo $metaInfo ['domain']; ?>/banners/<?php 
                        if ($available == 1){
                            echo $banner ?? 'banners/default.png';
                        }
                        else {
                            echo "banners/default.png";
                        }?>"
                        alt="banner" />
                    </div>
                    <?php
                    if ($found == 1 && $available == 1){
                    ?>
                    <div class="a-body">
                        <div class="a-con">
                            <div class="a-title">
                                <?php echo $title;?>
                            </div>
                            <div class="a-cat"><?php echo $category; ?></div>
                            <div class="po-sl">
                                <div class="a-pd"><?php echo $date; ?></div>
                                <div class="a-com-view">
                                    <?php echo $views; ?> <i class="fa fa-eye" aria-hidden="true" style="margin-right: 10px;"></i>
                                    <?php echo $numberOfComments; ?> <i class="fa fa-comments-o" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                        <div class="ad-mo">
                            
                            <div class="ad-pi">
                                <a href="<?php echo $metaInfo ['domain'].'/view-profile/'.$user->userName($auid); ?>">
                                    <img src="<?php echo $metaInfo ['domain'].'/profileImages/'.$profileImage; ?>" alt="Profile">
                                </a> 
                                <?php if ($userLevel == 4 || $userLevel == 5){
                                ?>   
                                <span><i class="fa fa-star" aria-hidden="true"></i></span>
                                <?php
                                }?>
                            </div>
                            <div class="ad-na">
                                <a href="<?php echo $metaInfo ['domain'].'/view-profile/'.$user->userName($auid); ?>"><?php echo $authorName; ?></a>
                            </div>
                        </div>
                        <div class="ab-con">
                            <?php echo $content; ?>
                        </div>
                    </div>
                    <?php
                    }
                    elseif ($available !== 1){
                        echo '<div style="color: var(--textHM);">This post has been deleted. <br />
                        Click <a href="'.$metaInfo['domain'].'/index.php">here</a> to go to homepage.</div>
                        ';
                    }
                    else{
                        echo '<div style="color: var(--textHM);">Post not found</div>';
                    }
                    ?>
                    <?php 
                    if (isset($_SESSION['loggedIn'])){
                        if ($_SESSION['loggedIn'] == true){
                    ?>
                    <div class="a-options">
                        <?php 
                        if ($loggedIn == true && ($auid == $_SESSION['uid']) && 
                        ($user->userLevel($_SESSION['uid']) != 4 && $user->userLevel($_SESSION['uid']) != 5)){
                            echo '
                                <span><a href="'.$metaInfo['domain'].'/edit-post.php?pid='.$pid.'">Edit</a></span>
                                <span><a href="javascript:delpost('.$pid.')">Delete</a></span>
                            ';
                        }
                        
                        if ($loggedIn == true
                        && (($user->userLevel($_SESSION['uid']) == 4 || $user->userLevel($_SESSION['uid']) == 5) && $available == 1)){
                        echo'
                            <span><a href="'.$metaInfo['domain'].'/admin/feature-posts.php?pid='.$pid.'">Feature</a></span>
                            <span><a href="'.$metaInfo['domain'].'/edit-post.php?pid='.$pid.'">Edit</a></span>
                            <span><a href="javascript:delpost('.$pid.')">Delete</a></span>
                            
                            </div>
                        ';
                    
                        }
                    }
                }
                    ?>
                    
                </article>
                <?php
                if ($found == 1 && $available == 1){
                ?>
                <div class="c-com">
                    <span>Comments &nbsp; <i class="fa fa-arrow-down" aria-hidden="true" style="transform: scale(0.8, 1.4);"></i></span>
                </div>
                <div class="comments">
                    <div class="comments-panel">
                        <?php
                        if ($numberOfComments <= 0){
                        ?>
                        <div class="c-p-i cfs" style="color: var(--textHM)">
                            There are no comments on this post. Be the first one to comment.
                        </div>
                        <?php
                        }                            
                        ?>
                    </div>
                    <?php
                    if ($loggedIn == true && $allowCom != 'disabled' && $canComment == 1){
                    ?>
                    <div class="c-area">
                        <textarea name="comment-area" id="commentarea" placeholder="Enter your comment" maxlength="850"></textarea>
                        <button type="submit" name="comment-btn" id="comment-btn">Reply</button>
                    </div>
                    <?php 
                    }
                    if ($allowCom == 'disabled'){
                        echo '
                        <div class="c-area" style="color: var(--textM); margin: auto;">
                            Comments have been disabled
                        </div>
                        ';
                    }
                    if ($loggedIn == false && $allowCom != 'disabled'){
                    ?>
                    <div class="ua-c">
                        <span>You are not logged in. Please login to comment on this post.</span>
                        <ul>
                            <li><a href="<?php echo $metaInfo ['domain'].'/login.php'?>">Login</a></li>
                            <li><a href="<?php echo $metaInfo ['domain'].'/signup.php'?>">Signup</a></li>
                        </ul>
                    </div>
                    <?php
                    }                    
                    ?>
                </div>
                <?php
                }                
                ?>
            </div>
            <div class="right">
                <div class="top aimo">
                    <h4 class="h4">Author Info</h4>
                    <div class="a-info">
                        <div class="a-inf">
                            <div class="avtar">
                                <a href="<?php echo $metaInfo ['domain'].'/view-profile/'.$user->userName($auid); ?>"><img src="<?php echo $metaInfo ['domain']; ?>/profileImages/<?php echo $profileImage;?>" alt="profile picture"></a>
                                <?php
                                if ($userLevel == 4 || $userLevel == 5){
                                ?>
                                <div class="ad-pi">
                                    <span><i class="fa fa-star" aria-hidden="true"></i></span>
                                </div>
                                <?php
                                }
                                ?>
                            </div>
                            <div class="a-inf-n">
                                <div class="a-name">
                                    <a href="<?php echo $metaInfo ['domain'].'/view-profile/'.$user->userName($auid); ?>"><?php echo $authorName; ?></a>
                                </div>
                                <div class="a-lvl">
                                    <?php echo $userGroup; ?>
                                </div>
                            </div>
                        </div>
                        <div class="a-sm">
                            <span>Follow me on my social media</span>
                            <ul class="a-sm-l">
                                <li><a href="<?php echo $fbLink; ?>"><i title="facebook profile" class="fa fa-facebook"></i></a></li>
                                <li><a href="<?php echo $twLink; ?>"><i title="Twitter profile" class="fa fa-twitter"></i></a></li>
                                <li><a href="<?php echo $igLink; ?>"><i title="Instagram profile" class="fa fa-instagram"></i></a></li>
                                <li><a href="<?php echo $lnLink; ?>"><i title="Linkedin profile" class="fa fa-linkedin"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="top">
                <h4 class="h4">Popular Posts of <?php echo $authorName;?></h4>
                <?php
                //Getting top posts of a user
                $topPosts = $user->userTopPosts($auid, 2);
                ?>
                    <ul class="news">
                        <?php 
                        foreach ($topPosts as $row){
                            echo '
                            <div class="srrba" data-slug="'.$row['slug'].'">
                                <a href="'.$metaInfo['domain'].'/view-post/'.$row['slug'].'"><img src="'.$metaInfo['domain'].'/banners/'.$row['pbanner'].'">
                                    <div class="srrba-t">
                                        <p>
                                            '.($row['ptitle']).'
                                        </p>
                                    </div>
                                </a>
                            </div>
                            ';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>

        <?php
        if ($found == 1 && $available == 1){
        ?>
        <!-- The social media icon bar -->
        <div class="icon-bar">
            <a href="<?php echo $fb; ?>" class="facebook"><i class="fa fa-facebook"></i></a>
            <a href="<?php echo $tw; ?>" class="twitter"><i class="fa fa-twitter"></i></a>
            <a href="<?php echo $tg; ?>" class="telegram"><i class="fa fa-telegram"></i></a>
            <a href="<?php echo $wa; ?>" class="whatsapp"><i class="fa fa-whatsapp"></i></a>
        </div>

        <div class="share-sm">
            <div class="share-btn">
                <span><i class="fa fa-share-alt" aria-hidden="true"></i></span>
                <ul>
                    <li><a href="<?php echo $wa; ?>"><i class="fa fa-whatsapp" aria-hidden="true"></i>Share on WhatsApp</a></li>
                    <li><a href="<?php echo $tg; ?>"><i class="fa fa-telegram" aria-hidden="true"></i>Share on Telegram</a></li>
                    <li><a href="<?php echo $fb; ?>"><i class="fa fa-facebook" aria-hidden="true"></i>Share on Facebook</a></li>
                    <li><a href="<?php echo $tw; ?>"><i class="fa fa-twitter" aria-hidden="true"></i>Share on Twitter</a></li>
                </ul>
            </div>
        </div>
        <?php
        }
        ?>

    </main>
    
    <footer>
        <?php 
        include 'includes/footer.php';
        ?>
    </footer>


</body>

<script
    src="https://code.jquery.com/jquery-3.5.1.js"
    integrity="sha256-QWo7LDvxbWT2tbbQ97B53yJnYU3WhH/C8ycbRAkjPDc="
    crossorigin="anonymous">
</script>
    <?php

    //Include dark mode script
    include "includes/dark_mode_script.php";
    //Include navigation bar script
    include "includes/nav_toggle_js.php";

    //Include notifications
    include 'load-notifications.php';
    
    ?>
<script language="JavaScript" type="text/javascript">

$(document).ready(function(){                
    $('.c-com').click(function(){
        $(".comments").slideToggle();
    });
});
<?php if ($loggedIn == true){
    ?>
    function delpost(id){
        if (confirm("Are you sure you want to delete this post?")){
            window.location.href = '<?php echo $metaInfo['domain'];?>/delete_post.php?pid=' + id;
        }
    }
    <?php
}
?>

    <?php 
    if ($loggedIn == true && ($user->userLevel($_SESSION['uid']) == 4 || $user->userLevel($_SESSION['uid']) == 5)){
    ?>
    $(document).ready(function(){
        function delcomment(id){
            if (confirm("Are you sure you want to delete this comment?")){
                var action = 'delete-comment';
                
                alert('hi');
                $.ajax({
                    url: '<?php echo $metaInfo['domain']; ?>/comments.php',
                    type: 'post',
                    data: {
                        comment_id: 1,
                        action: action
                    },

                    success: function(){
                        showComments();
                    }
                })
            }
        }
    });
        <?php  
    }
    ?>
</script>
<?php 
if ($found == 1 && $allowCom != 'disabled'){
?>
<script>
    $(document).ready(function(){

        var no_of_comments = <?php echo $numberOfComments;?>

        $('#comment-btn').click(function(){
            var comment = $('#commentarea').val();
            var action = 'add-comment';

            if (comment != ''){
                $.ajax({
                    url: '<?php echo $metaInfo['domain']; ?>/comments.php',
                    type: 'post',
                    data: {
                        comment: comment,
                        action: action,
                        pid: <?php echo $pid;?>
                    },

                    success: function(data){
                        $('.comments-panel').append(data);
                        $('.cfs').html('');
                        $('#commentarea').val('');
                    }
                });
            }

        });

        $('.srrba').click(function(){
            window.location.assign('<?php echo $metaInfo['domain'];?>/view-post/'+ $(this).data('slug'));
        });

        showComments();

        function showComments(page = 1){
            if (no_of_comments > 0){

                var action = 'show-comments';

                $.ajax({
                    url: '<?php echo $metaInfo['domain']; ?>/comments.php',
                    type: 'post',
                    data: {
                        action: action,
                        pid: <?php echo $pid; ?>,
                        page: page
                    },

                    success: function(data){
                        $('.comments-panel').html(data);
                        $('.number').click(function(){
                            showComments($(this).data('page'));
                        });
                        <?php
                        if ($loggedIn == true){
                        if ($user->userLevel($_SESSION['uid']) == 4 || $user->userLevel($_SESSION['uid']) == 5){
                        ?>
                        $('.del-com').click(function(){
                            const action = 'delete-comment';
                            var cid = $(this).data('cid');
                            if (confirm('Are you sure you want to delete the comment')){
                                $.ajax({
                                    url: '<?php echo $metaInfo['domain']; ?>/comments.php',
                                    type: 'post',
                                    data: {
                                        action: action,
                                        cid: cid,
                                        pid: <?php echo $pid;?>
                                    },
                                    success: function(response){
                                        if (response == 1){                                            
                                            showComments();
                                        }
                                        if (response == 0){
                                            alert('Cannot delete comment');
                                        }
                                    }
                                });
                            }
                        });
                        <?php
                        }
                        }
                        ?>
                    }

                });
            }
        }
    });
</script>

<script>
        function user(){
            alert('Not set by user');
        }
    </script>
<?php
}
?>
</html>

