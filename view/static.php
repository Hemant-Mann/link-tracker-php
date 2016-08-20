<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta http-equiv="X-Frame-Options" content="deny">
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="article">
    <meta property="og:title" content="VMTraffic" />
    <meta property="og:description" content="A network for real publishers developed by <?php echo DOMAIN; ?>">
    <meta property="og:url" content="<?php echo URL;?>">
    <meta property="og:image" content="http://<?php echo DOMAIN;?>/public/logo.png">
    <meta property="og:site_name" content="<?php echo PLATFORM; ?>">
    <meta property="article:section" content="Pictures" />
    
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php echo PLATFORM; ?>">
    <meta name="twitter:description" content="A real Network for real publishers developed by <?php echo DOMAIN; ?>">
    <meta name="twitter:url" content="<?php echo SITE;?>">
    <meta name="twitter:image" content="http://<?php echo DOMAIN;?>/public/logo.png">
</head>

<body>
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
window.location.href = 'http://' + '<?php echo $domain; ?>';
</script>
</body>

</html>