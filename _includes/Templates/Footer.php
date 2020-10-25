<?php
# Copyright (c) 2007-2020 Linh Pham
# wwdt.me_v3 is relased under the terms of the Apache License 2.0
?>

</div>
<div id="footer">
<script>
  (function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/platform.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
  })();
</script>
<p>
Copyright &copy; 2007&ndash;<?php print date('Y');?> <a href="http://linhpham.org/">Linh Pham</a>.
All Rights Reserved. Not affiliated with NPR, WBEZ or Urgent Haircut Productions.<br>
For more information about me or the website, please visit the <a href="/about">About</a> page.
</p>
<p>
<p>WWDTM Stats Page Version: <?php print VERSION; ?><br>
<em>Page Served: <?php print date('Y-m-d H:i:s O'); ?> (<?php print round((microtime(true) - $startTime), 3);?>s)</em>
</p>
</div>
</div>
</body>
</html>
