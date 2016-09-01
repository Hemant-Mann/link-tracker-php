<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta http-equiv="X-Frame-Options" content="deny">
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?php echo $link->title;?>" />
    <meta property="og:description" content="<?php echo $link->description;?>">
    <meta property="og:url" content="<?php echo URL;?>">
    <meta property="og:width" content="<?php echo $link->width;?>">
    <meta property="og:height" content="<?php echo $link->height;?>">
    <meta property="og:image" content="<?php echo $link->image;?>">
    <meta property="og:site_name" content="<?php echo PLATFORM; ?>">
    <meta property="article:section" content="Pictures" />
    
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php echo $link->title;?>">
    <meta name="twitter:description" content="<?php echo $link->description;?>">
    <meta name="twitter:url" content="<?php echo URL;?>">
    <meta name="twitter:image" content="<?php echo $link->image;?>">
</head>

<body>
<?php $divs = rand(1, 4); ?>
<?php for ($i = 1; $i <= $divs; $i++) { ?>
    <div>
<?php } ?>
<script type="text/javascript">
(function () {
    var ad='<?php echo (int) $link->ad; ?>', _id='<?php echo $link->__id ?>', img=new Image();
    img.src = 'http://' + '<?php echo $_SERVER["HTTP_HOST"] ?>' + '/a/v/blue.gif' + '?' + 'a=' + ad + '&_id=' + _id;
}());
</script>
<?php for ($i = 1; $i <= $divs; $i++) { ?>
    </div>
<?php } ?>
<?php if (GAID): ?>
  <script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
    ga('create', '<?php echo GAID; ?>', 'auto');
    ga('send', 'pageview');
  </script>
<?php endif ?>

<script type="text/javascript">
(function () {
    window.location.href = '<?php echo $link->url; ?>';
}());
</script>
</body>

</html>