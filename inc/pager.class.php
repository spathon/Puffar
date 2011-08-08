<?php
class Pager {
    protected $pages;
    protected $perpage;
    protected $page;
	protected $get_var;
	protected $post_type;

    public function __construct($perpage, $page, $tot, $post_type) {
        $this->perpage = $perpage;
        $this->page = $page;
        $this->pages = ceil($tot/$perpage);
		$this->get_var = 'ps_pager_'.$post_type;
		$this->post_type = $post_type;
    }

    protected function getFirstPageURL() {
        return UrlUtils::appendQueryString($this->get_var, 1);
    }

    protected function getPrevPageURL() {
        return UrlUtils::appendQueryString($this->get_var, $this->page == 1 ? 1 : $this->pagenum - 1);
    }

    protected function getNextPageURL() {
        return UrlUtils::appendQueryString($this->get_var, $this->page == $this->pages ? $this->pages : $this->page + 1);
    }

    protected function getLastPageURL() {
        return UrlUtils::appendQueryString($this->get_var, $this->pages);
    }

    protected function getPageURL($page) {
        return UrlUtils::appendQueryString($this->get_var, $page);
    }

    public function generatePagination($range = 2) {
    	
		// if there is only one pag then return
		if($this->pages <= 1 ) return;
		
        $shown = $range * 2 + 1;
        ?>
        <div class="tablenav spathon-where-pager" data-post-type="<?php echo $this->post_type; ?>">
            <div class="tablenav-pages">
            	<?php /*
                <span class="displaying-num"><?php echo $this->pages .' '. __('pages'); ?></span>
                <a href="<?php echo $this->getFirstPageURL(); ?>" <?php if ($this->page == 1) echo 'class="disabled"';  ?>>&laquo;</a>
                <a href="<?php echo $this->getPrevPageURL(); ?>" <?php if ($this->page == 1) echo 'class="disabled"';  ?>>&lsaquo;</a> 
				*/ ?>
                <?php
                for ($i = 1; $i <= $this->pages; $i++) { 
                    if ($this->pages != 1 &&( !($i >= $this->pages + $shown || $i <= $this->page - $shown) || $this->pages <= $shown )) {
                        echo '<a href="' . $this->getPageURL($i) . '" data-id="'. ($i - 1) .'" ' . ($i == $this->page ? 'class="disabled"' : '' ) . '>' . $i . '</a>';
                    }
                }
                ?>
                <?php /*
                <a href="<?php echo $this->getNextPageURL(); ?>" <?php if ($this->page == $this->pages) echo 'class="disabled"';  ?>>&raquo;</a>
                <a href="<?php echo $this->getLastPageURL(); ?>" <?php if ($this->page == $this->pages) echo 'class="disabled"';  ?>>&rsaquo;</a>
				 */ ?>
			</div>
        </div>
        <?php
    }

}

class UrlUtils {

    public static function parseQueryString($url = '') {
        $queryArr = array();
        $queryStr = $_SERVER['QUERY_STRING'];
        if (!empty($url)) {
            $queryStr = parse_url($url, PHP_URL_QUERY);
        }
        parse_str($queryStr, $queryArr);
        return $queryArr;
    }

    public static function appendQueryString($key, $val, $url = "") {
        $queryArr = self::parseQueryString($url);
        $queryArr[$key] = $val;
        $queryStr = http_build_query($queryArr);

        $url = empty($url) ? $_SERVER['PHP_SELF'] : $url;
        $parts = parse_url($url);
        $parts['query'] = $queryStr;

        return $parts['path'] . '?' . $parts['query'];
    }

    public static function removeQueryString($remove) {
        $queryArr = self::parseQueryString();
        if (is_array($remove)) {
            foreach ($remove as  $r) {
                if (array_key_exists($r, $queryArr)) {
                    unset($queryArr[$r]);
                }
            }
        } else {
            if (array_key_exists($remove, $queryArr)) {
                unset($queryArr[$remove]);
            }
        }

        $queryStr = http_build_query($queryArr);
        return $_SERVER['PHP_SELF'] . '?' . $queryStr;
    }

}
