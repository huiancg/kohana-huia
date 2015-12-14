## Analytics

Para evitar a possibilidade de registrar dados em ambientes incorretos, existe a classe de Analytics que retorna um template relacionado a um analytics. É possível adicionar N templates diferentes, primariamente descida utilizar o metodo, como descrito abaixo:

~~~
<html>
<body>
	<?php echo Analytics::render(); ?>
</body>
</html>
~~~

### Configuração

Por padrão o Analytics é usado nos projetos, então a configuração abaixo, com a conta relacionada ao ambiente:
~~~
// application/config/huia/analytics.php
return array(
	'default' => array(
		'account' => (Kohana::$environment = Kohana::PRODUCTION) ? 'UA-XXXXXXXX-X' : 'UA-XXXXXXXX-X',
		'href'    => 'stats.g.doubleclick.net/dc.js',
	),
);
~~~

### Views

O caminho das views é `huia/analytics` seguido do nome da configuração.

~~~
// application/views/huia/analytics/default.php
<script type="text/javascript">
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', '<?php echo $account; ?>']);
	_gaq.push(['_trackPageview']);
	(function () {
		var ga = document.createElement('script');
		ga.type = 'text/javascript';
		ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + '<?php echo $href ?>';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
</script>
~~~

A outra configuração já integrada é a do Google Tag Manager

~~~
// application/views/huia/analytics/google-tag-manager.php
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=<?php echo $account; ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>
	(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','<?php echo $account; ?>');
</script>
~~~