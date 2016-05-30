<!DOCTYPE html>
<html>
<head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article#">
    <meta http-equiv="X-Frame-Options" content="deny">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?php echo $track->link->title;?>" />
    <meta property="og:description" content="<?php echo $track->link->description;?>">
    <meta property="og:url" content="<?php echo URL;?>">
    <meta property="og:image" content="https://dh3fr73b75uve.cloudfront.net/images/resize/<?php $img = explode(".", $track->link->image); echo $img[0]."-600x315.".$img[1];?>">
    <meta property="og:site_name" content="<?php $parse = parse_url($track->link->url); echo $parse["host"];?>">
    <meta property="article:section" content="Pictures" />
    
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php echo $track->link->title;?>">
    <meta name="twitter:description" content="<?php echo $track->link->description;?>">
    <meta name="twitter:url" content="<?php echo URL;?>">

    <title><?php echo $track->link->title;?></title>
    <meta property="fb:app_id" content="583482395136457" />
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <style type="text/css">
        .data {
            display: none;
        }
    </style>
</head>
<body>
<h1 class="data"><?php echo $track->link->title;?></h1>
<p class="data"><?php echo $track->link->description;?></p>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-74080200-2', 'auto');
  ga('send', 'pageview');
</script>
<script type="text/javascript">
process();
function process() {
    $.ajax({
        url: 'includes/process.php',
        headers: { 'Clicks99Track': "<?php echo uniqid();?>" },
        type: 'GET',
        cache: true,
        data: {id: "<?php echo $_GET['id'];?>"},
        success: function (data) {
            window.location = '<?php echo $track->redirectUrl();?>';
        }
    });
}
</script>
</body>
</html>