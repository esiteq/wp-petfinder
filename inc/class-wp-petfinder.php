<?php
namespace WP_Petfinder;

/**
 * WP_Petfinder
 *
 * @package
 * @author ESITEQ
 * @copyright Alex Raven
 * @version 2019
 * @access public
 */

class WP_Petfinder
{
    const DEBUG = false;
    const GROUP = 'PetfinderAPIv2';
    const VERSION = '0.1';
    const ROLE = 'edit_others_posts';
    public $paged = 1;
    public $limit = 20;
    public $pages_to_show = 7;
    private $view = 'grid';
    private $view_only = false;
    private $paginator;
    private $api;
    //private $api_key, $api_secret;
    private $options;
    private $types;
    private $pagination;
    public  $source = '';
    public  $animals_cache_expire = 3600 * 24;  // cache expires in 24 hours
    private $ad_link, $page_title;
    public  $animal, $breeds;
    /**
     * WP_Petfinder::__construct()
     * Class constructor. Has no input.
     * @return void
     */
    public function __construct()
    {
        $this->api = new Petfinder_API($this->get_option('api_key', ''), $this->get_option('api_secret', ''));
        add_shortcode('pf_search_form', [$this, 'pf_search_form']);
        add_shortcode('pf_search_results', [$this, 'pf_search_results']);
        add_shortcode('pf_details', [$this, 'pf_details']);
        add_shortcode('pf_animal', [$this, 'pf_animal']);
        add_action('init', [$this, 'init']);
        add_action('plugins_loaded', [$this, 'plugins_loaded']);
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('widgets_init', [$this, 'widgets_init']);
        add_action('wp_enqueue_scripts', [$this, 'wp_enqueue_scripts'], 100);
        add_action('wppf_before_search_results', [$this, 'wppf_before_search_results']);
        add_action('wppf_after_search_results', [$this, 'wppf_after_search_results']);
        add_action('wppf_animal_gallery', [$this, 'wppf_animal_gallery']);
        add_filter('the_content', [$this, 'the_content']);
        add_filter('document_title_parts', [$this, 'document_title_parts']);
        add_filter('the_title', [$this, 'the_title'], 10, 2);
        add_action('admin_notices', [$this, 'admin_notices']);
        add_action('wp_head', [$this, 'wp_head']);
        $this->get_current_page();
    }
    /**
     * WP_Petfinder::adopt_button()
     * Displays Adopt Me button
     * @param array $animal
     * @return void
     */
    public function adopt_button($animal)
    {
        if ($animal['status'] != 'adopted')
        {
?>
        <a href="<?php echo esc_attr($this->adopt_link($animal)); ?>" class="wppf-button wppf-button-adopt"><?php _e('Adopt Me!', 'wppf'); ?></a>
<?php
        }
    }
    /**
     * WP_Petfinder::d()
     * Display debug message (if Kint is installed and active)
     * @param mixed $var
     * @return boolean
     */
    public function d($var1 = NULL, $var2 = NULL)
    {
        if (self::DEBUG == false) return false;
        if (class_exists('Kint'))
        {
            if (is_null($var2))
            {
                \Kint::dump($var1);
            }
            else
            {
                \Kint::dump($var1, $var2);
            }
        }
        return true;
    }
    /**
     * WP_Petfinder::debug()
     * Puts debug message to debug.txt
     * @param string $msg
     * @return
     */
    function debug($msg)
    {
        if (self::DEBUG == false) return;
        return file_put_contents(WPPF_DIR. '/debug.txt', $msg. "\n", FILE_APPEND);
    }
    /**
     * WP_Petfinder::wp_head()
     *
     * @return void
     */
    public function wp_head()
    {
        if ($this->get_option('custom_css', '') != '')
        {
?>
<style type="text/css">
<?php echo $this->get_option('custom_css', ''); ?>
</style>
<?php
        }
    }
    /**
     * WP_Petfinder::admin_notices()
     *
     * @return void
     */
    public function admin_notices()
    {
        /*
         * We will use a trick to test if persistent object cache is enabled.
         * Maybe Wordpress has a function for that, but I couldn't find it.
         */

        global $wp_query;
        $md5 = md5(serialize(get_option('active_plugins')));
        if ($md5 != get_option('wppf_active_plugins'))
        {
            delete_option('wppf_cache_test');
        }
        if (empty(get_option('wppf_cache_test')))
        {
            update_option('wppf_cache_test', $_SERVER['REQUEST_URI'], 'yes');
            update_option('wppf_active_plugins', $md5, 'yes');
            wp_cache_add('wppf_cache_test', time(), self::GROUP, $this->animals_cache_expire);
        }
        else
        {
            if ($_SERVER['REQUEST_URI'] != get_option('wppf_cache_test'))
            {
                $test = wp_cache_get('wppf_cache_test', self::GROUP, true);
                $cache = ($this->get_option('cache', '0') == '1');
                if (is_numeric($test) && $cache == true)
                {
?>
<div class="notice notice-info is-dismissible">
	<p><?php _e('We noticed that you have activated persistent object cache plugin (such as Redis Object Cache), but also have enabled this plugin\'s internal cache. You may want to disable internal caching to improve performance. To do that, turn internal cache off in plugin options ', 'wppf'); ?> <a href="admin.php?page=wppf_options"><?php _e('here', 'wppf'); ?></a>.</p>
</div>
<?php
                }
            }
        }
        // check animal detail page
        if ($this->get_option('animal_page', 0) == 0)
        {
?>
<div class="notice notice-error is-dismissible">
	<p><?php _e('You have not set Animal details page, so clicking animal link in search results will have no effect. Please fix it ', 'wppf'); ?> <a href="admin.php?page=wppf_options"><?php _e('here', 'wppf'); ?></a>.</p>
</div>
<?php
        }
        if ($this->get_option('api_key', '') == '' || $this->get_option('api_secret', '') == ''):
?>
<div class="notice notice-error is-dismissible">
	<p><?php _e('You have not set Petfinder API key and/or Petfinder API secret.', 'wppf'); ?> <a href="admin.php?page=wppf_options"><?php _e('Click this link to fix', 'wppf'); ?></a>.</p>
</div>
<?php
        endif;
    }
    /**
     * WP_Petfinder::the_title()
     * Update Animal Detail page title
     * @param string $title
     * @param integer $post_id
     * @return string
     */
    public function the_title($title, $post_id)
    {
        if (in_the_loop() && $this->page_title && $this->is_animal_page())
        {
            $title = $this->page_title;
        }
        return $title;
    }
    /**
     * WP_Petfinder::get_animal_permalink()
     * Returns link to Animal Detail page by Animal id
     * @param integer $id
     * @return string
     */
    private function get_animal_permalink($id)
    {
        if (is_array($id) && is_numeric($id['id']))
        {
            $id = $id['id'];
        }
        if (!$this->ad_link)
        {
            $page = $this->get_option('animal_page', 0);
            if ($page > 0)
            {
                $this->ad_link = get_permalink($page);
            }
            else
            {
                $this->ad_link = '#';
            }
        }
        if ($this->permalinks_active())
        {
            if ($this->ad_link == '#')
            {
                $link = $this->ad_link;
            }
            else
            {
                $link = $this->ad_link . $id. '/';
            }
            return $link;
        }
        if ($this->ad_link == '#')
        {
            $link = $this->ad_link;
        }
        else
        {
            $link = $this->ad_link . '&id='. $id;
        }
        return $link;
    }
    /**
     * WP_Petfinder::get_current_page()
     * Returns current page based on page URI
     * @return void
     */
    private function get_current_page()
    {
        $this->paged = 1;
        $tmp = explode('/', $_SERVER['REQUEST_URI']);
        if (isset($_GET['page']))
        {
            // sanitize
            $this->paged = intval($_GET['page']);
        }
        else
        {
            if (is_array($tmp))
            {
                $end = end($tmp);
                if (substr($end, 0, 1) == '?')
                {
                    array_pop($tmp);
                }
                $end = end($tmp);
                if (!$end)
                {
                    array_pop($tmp);
                }
                $end = end($tmp);
                if (is_numeric($end))
                {
                    $this->paged = intval($end);
                }
            }
        }
        if ($this->paged < 1)
        {
            $this->paged = 1;
        }
    }
    /**
     * WP_Petfinder::get_pagination()
     * Returns pagination array from previous call of get_animals() or get_organizations()
     * @return
     */
    public function get_pagination()
    {
        return $this->pagination;
    }
    /**
     * WP_Petfinder::wppf_animal_gallery()
     * Outputs animal gallery with navigation and lightbox
     * @param mixed $animal
     * @return void
     */
    public function wppf_animal_gallery($animal)
    {
        require $this->locate_template('animal-gallery-slick.php');
    }
    public function adopt_link()
    {
        $animal_id = $this->get_animal_id();
        $animal = $this->get_animal($animal_id);
        $type = strtolower($animal['type']);
        $adopt_page_cat = $this->get_option('adopt_page_cat', 0);
        $adopt_page_dog = $this->get_option('adopt_page_dog', 0);
        $adopt_page = 0;
        if ($adopt_page_cat > 0 && $type == 'cat')
        {
            $adopt_page = $adopt_page_cat;
        }
        if ($adopt_page_dog > 0 && $type == 'dog')
        {
            $adopt_page = $adopt_page_dog;
        }
        if ($adopt_page > 0)
        {
            $adopt_page = get_permalink($adopt_page);
            return $adopt_page. '?animal_id='. $animal_id. '&animal_name='. $animal['name']. '&animal_type='. $animal['type'];
        }
        return '#';
    }
    /**
     * WP_Petfinder::has_gallery()
     * returns true if animal has gallery
     * @param mixed $animal
     * @return
     */
    public function has_gallery($animal)
    {
        return is_array($animal['photos']) && count($animal['photos']) > 0;
    }
    /**
     * WP_Petfinder::get_view()
     * Returns current gallery view
     * @return string
     */
    public function get_view()
    {
        // sanitize
        return isset($_GET['view']) ? sanitize_text_field($_GET['view']) : $this->view;
    }
    /**
     * WP_Petfinder::wppf_before_search_results()
     * Action called before search results
     * @return void
     */
    public function wppf_before_search_results()
    {
        if ($this->view_only) return;
        $view = $this->get_view();
?>
<div class="wppf-view-links"><a class="<?php echo ($view == 'grid') ? 'active' : ''; ?>" href="<?php echo esc_attr($this->get_view_uri('grid')); ?>">Grid view</a> | <a class="<?php echo ($view == 'list') ? 'active' : ''; ?>" href="<?php echo esc_attr($this->get_view_uri('list')); ?>">List view</a></div>
<?php
    }
    /**
     * WP_Petfinder::wppf_after_search_results()
     * Action called after search results
     * @return void
     */
    public function wppf_after_search_results()
    {
        require_once $this->locate_template('search-results-pagination.php');
    }
    /**
     * WP_Petfinder::wp_enqueue_scripts()
     * Enqueues all styles and scripts for this plugin
     * @return void
     */
    public function wp_enqueue_scripts()
    {
        $ver = (self::DEBUG == true) ? time() : self::VERSION;
        wp_enqueue_style( 'driveway', $this->plugins_url('css/driveway.min.css'));
        wp_enqueue_style( 'slick', $this->plugins_url('css/slick.css'));
        wp_enqueue_script('slick', $this->plugins_url('js/slick.min.js'), ['jquery'], self::VERSION, true);
        //wp_enqueue_style('material-icons', 'https://fonts.googleapis.com/icon?family=Material+Icons', [], $ver);
        /*
        wp_enqueue_style( 'fancybox', $this->plugins_url('css/jquery.fancybox.min.css'));
        wp_enqueue_script('fancybox', $this->plugins_url('js/jquery.fancybox.min.js'), ['jquery'], self::VERSION);
        wp_enqueue_script('formstone',      $this->plugins_url('js/site.js'), ['jquery'], self::VERSION);
        wp_enqueue_style( 'carousel',       $this->plugins_url('css/carousel.css'), []);
        wp_enqueue_script('carousel',       $this->plugins_url('js/carousel.js'), ['jquery'], self::VERSION);
        */
        wp_enqueue_style( 'magnific-popup', $this->plugins_url('css/magnific-popup.css'));
        wp_enqueue_script('magnific-popup', $this->plugins_url('js/jquery.magnific-popup.min.js'), ['jquery'], $ver);
        wp_enqueue_style( 'wp-petfinder',   $this->plugins_url('css/wp-petfinder.css'), [], $ver);
        wp_enqueue_script('wp-petfinder',   $this->plugins_url('js/wp-petfinder.js'), ['jquery'], $ver);
    }
    /**
     * WP_Petfinder::array_hash()
     * Used to make cache keys
     * @param mixed $array
     * @return
     */
    private function array_hash($array)
    {
        return is_array($array) ? md5(serialize($array)) : md5($array);
    }
    /**
     * WP_Petfinder::cache_set()
     * Cache wrapper for wp_cache_add
     * @param mixed $key
     * @param mixed $value
     * @param integer $expire
     * @return
     */
    private function cache_set($key, $value, $expire = 0)
    {
        global $wpdb;
        if ($expire == 0)
        {
            $expire = $this->animals_cache_expire;
        }
        if ($this->get_option('cache', '1') == '1')
        {
            $sql = $wpdb->prepare("INSERT INTO {$wpdb->prefix}wpps_cache SET `cache_key`=%s, `contents`=%s, `expires`=%d", $key, serialize($value), time()+$expire);
            $wpdb->query($sql);
            return true;
        }
        return wp_cache_add($key, $value, self::GROUP, $expire);
    }
    /**
     * WP_Petfinder::cache_get()
     * Cache wrapper for wp_cache_get
     * @param mixed $key
     * @return
     */
    private function cache_get($key)
    {
        global $wpdb;
        if ($this->get_option('cache', '1') == '1')
        {
            $sql = $wpdb->prepare("SELECT * FROM {$wpdb->prefix}wpps_cache WHERE `cache_key`=%s LIMIT 1", $key);
            $row = $wpdb->get_row($sql, ARRAY_A);
            if (is_array($row))
            {
                if ($row['expires'] > time())
                {
                    return unserialize($row['contents']);
                }
                else
                {
                    $sql = $wpdb->prepare("DELETE FROM {$wpdb->prefix}wpps_cache WHERE `cache_key`=%s LIMIT 1", $key);
                    $wpdb->query($sql);
                }
            }
            return false;
        }
        return wp_cache_get($key, self::GROUP, true);
    }
    /**
     * WP_Petfinder::widgets_init()
     * Initialize widgets
     * @return
     */
    public function widgets_init()
    {
        register_widget('WPPF_Animals_From_Shelter');
        register_widget('WPPF_Search_Form');
    }
    /**
     * WP_Petfinder::get_animal_sizes_array()
     * Returns array of animal sizes
     * @param bool $default
     * @return array
     */
    public function get_animal_sizes_array($default = true)
    {
        $arr = [];
        if ($default)
        {
            $arr[''] = __('Any', 'wppf');
        }
        $arr['small']  = __('Small', 'wppf');
        $arr['medium'] = __('Medium', 'wppf');
        $arr['large']  = __('Large', 'wppf');
        $arr['xlarge'] = __('Huge', 'wppf');
        return $arr;
    }
    /**
     * WP_Petfinder::get_animal_types_array()
     * Returns array of animal types
     * @return
     */
    public function get_animal_types_array($default = true)
    {
        $arr = [];
        if ($default)
        {
            $arr[''] = __('Any', 'wppf');
        }
        $types = $this->get_animal_types();
        if (is_array($types))
        {
            foreach ($types as $type)
            {
                $arr[$type['name']] = $type['name'];
            }
        }
        return $arr;
    }
    /**
     * WP_Petfinder::get_comments()
     * Parse comments from template file
     * @param string $filename
     * @return
     */
    public function get_comments($filename)
    {
        $docComments = array_filter(token_get_all(file_get_contents($filename)), function($entry)
        {
            return $entry[0] == T_COMMENT;
        });
        $fileDocComment = array_shift($docComments);
        $regexp = "/\@.*\:\s.*\r/";
        preg_match_all($regexp, $fileDocComment[1], $matches);
        for($i = 0; $i < sizeof($matches[0]); $i++)
        {
            $params[$i] = explode(": ", $matches[0][$i]);
        }
        $args = [];
        if (is_array($params))
        {
            foreach ($params as $par)
            {
                if (is_array($par))
                {
                    $args[trim($par[0])] = trim($par[1]);
                }
            }
        }
        return($args);
    }
    /**
     * WP_Petfinder::get_view_uri()
     * Generate URI with specified view
     * @param string $view
     * @return
     */
    public function get_view_uri($view)
    {
        return $this->uri_var(['view'=>$view]);
    }
    /**
     * WP_Petfinder::is_grid()
     * Returns true if grid view is currently active
     * @return boolean
     */
    public function is_grid()
    {
        // not sanitizing because $_GET is used only for comparison here
        return isset($_GET['view']) ? ($_GET['view'] == 'grid' ? true : false) : true;
    }
    /**
     * WP_Petfinder::get_template_list()
     * Returns list of available templates by given mask
     * @param string $file
     * @return
     */
    public function get_template_list($file)
    {
        $file = trim(str_replace('.php', '', basename($file)));
        $path = WPPF_DIR. '/templates/widgets/*';
        $files = glob($path);
        $templates = ['default' => __('Default', 'wppf')];
        if (is_array($files))
        {
            foreach ($files as $file)
            {
                $param = $this->get_comments($file);
                if (isset($param['@template']))
                {
                    $templates[basename($file)] = $param['@template'];
                }
            }
        }
        return $templates;
    }
    /**
     * WP_Petfinder::uri_var()
     * Returns current URI with replaced variables
     * @param array $vars
     * @return
     */
    function uri_var($vars = [])
    {
        $tmp = explode('?', $_SERVER['REQUEST_URI']);
        $url = $tmp[0];
        parse_str($tmp[1], $args);
        if (is_array($args))
        {
            foreach ($vars as $key => $value)
            {
                $args[$key] = $value;
            }
            if (isset($args['page']))
            {
                unset($args['page']);
            }
            $query = http_build_query($args);
            if (isset($vars['page']))
            {
                if ($query)
                {
                    $query .= '&page='. $vars['page'];
                }
                else
                {
                    $query = '?page='. $vars['page'];
                }
            }
        }
        return $url . '?' . $query;
    }
    /**
     * WP_Petfinder::pager()
     * Outputs pagination HTML from given template
     * @param string $tpl
     * @return void
     */
    function pager($tpl = 'normal')
    {
        global $paginator;
        if (!isset($this->paginator))
        {
            $this->paginator();
        }
        $paginator = $this->paginator;
        $paginator->setMaxPagesToShow($this->pages_to_show);
        require_once $this->locate_template('search-results-pager-'. $tpl. '.php');
    }
    /**
     * WP_Petfinder::permalinks_active()
     * Returns current permalink structure, or empty string if permalinks are off
     * @return
     */
    private function permalinks_active()
    {
        return get_option('permalink_structure', '');
    }
    /**
     * WP_Petfinder::paginator()
     * Creates Paginator object from previous call of get_animals() or get_organizations()
     * @return void
     */
    function paginator()
    {
        require_once WPPF_DIR. '/inc/Paginator.php';
        $pag = $this->get_pagination();
        if (is_array($pag))
        {
            if ($this->permalinks_active())
            {
                $tmp = explode('/', $_SERVER['REQUEST_URI']);
                if (end($tmp) == '')
                {
                    array_pop($tmp);
                }
                if (!is_numeric(end($tmp)))
                {
                    $pattern = $_SERVER['REQUEST_URI']. '(:num)/';
                }
                else
                {
                    if (stristr($_SERVER['REQUEST_URI'], '/'. $this->paged. '/'))
                    {
                        $pattern = str_replace('/'. $this->paged. '/', '/(:num)/', $_SERVER['REQUEST_URI']);
                    }
                    else
                    {
                        $pattern = str_replace('/?', '/(:num)/?', $_SERVER['REQUEST_URI']);
                    }
                }
            }
            else
            {
                $pattern = $this->uri_var(['page'=>'(:num)']);
            }
            $paginator = new \JasonGrimes\Paginator($pag['total_count'], $this->limit, $this->paged, $pattern);
            $this->paginator = $paginator;
        }
    }
    /**
     * WP_Petfinder::wp_petfinder_page()
     * This page is for testing purposes only. Will be disabled on production.
     * @return
     */
    public function wp_petfinder_page()
    {
?>
<style type="text/css">
table.zebra tbody tr:nth-child(even) {
  background-color: #f0f0f0;
}
table.zebra td,
table.zebra th {
    padding:10px;
}
.td-photo {
    width:100px;
}
.td-id {
    white-space:nowrap;
}
.td-images {
    width:20px;
}
.td-name a {
    font-weight:bold;
}
.td-desc {
    width:25%;
}
.animal-thumb {
    background-position:center center;
    background-size:100px auto;
    background-repeat:no-repeat;
    background-color:white;
    border:1px solid #ccc;
    width: 100px;
    height:100px;
}
</style>
<?php
        $types = $this->get_animal_types();
        $types_s = $this->source;
        $animals_s = $this->source;
        $organizations = $this->get_organizations(['location' => 'New York']);
        $organization_s = $this->source;
        $animal = $this->get_animal(46404184);
        $animal_s = $this->source;
        $organization = $this->get_organization('NY606');
        $organization_s = $this->source;
        $breeds = $this->get_animal_breeds('cat');
        $breeds_s = $this->source;
        $types_a = $this->get_animal_types_array();
        $types_a_s = $this->source;
        //$desc = $this->get_description('https://www.petfinder.com/dog/henna-pr-choc-lab-dutch-shepx-46343725/ny/new-york/zanis-furry-friends-zff-inc-ny606/');
?>
<div class="wrap">
<?php
//d($breeds);
//$this->d($this->get_animal_permalink(46343725));
//$this->d($animal, $animal_s);
/*
d($types_a, $types_a_s);
d($types, $types_s);
d($breeds, $breeds_s);
d($organization, $organization_s);
d($organizations, $organization_s);
d($desc);
*/
        $animals = $this->get_animals(['organization'=>'NY606', 'type'=>'cat']);
?>
    <p>Get Animals (source: <?php echo esc_html($this->source); ?>)</p>
    <table class="widefat fixed zebra" cellspacing="0">
        <thead>
            <tr>
                <th class="td-photo">Photo</th>
                <th class="td-images">#</th>
                <th class="td-id">Id</th>
                <th class="td-name">Name</th>
                <th class="td-type">Type</th>
                <th class="td-gender">Gender</th>
                <th class="td-age">Age</th>
                <th class="td-breed">Breed</th>
                <th class="td-status">Status</th>
                <th class="td-desc">Description</th>
            </tr>
        </thead>
        <tbody>
<?php
foreach ($animals as $animal):
?>
            <tr>
                <td class="td-photo"><div class="animal-thumb" style="background-image:url(<?php echo sanitize_text_field($animal['thumbnail']); ?>);"></div></td>
                <td class="td-images"><?php echo count($animal['photos']); ?></td>
                <td class="td-id"><?php echo esc_html($animal['id']); ?></td>
                <td class="td-name"><a href="<?php echo $animal['url']; ?>" target="_blank"><?php echo esc_html($animal['name']); ?></a></td>
                <td class="td-type"><?php echo esc_html($animal['type']); ?></td>
                <td class="td-gender"><?php echo esc_html($animal['gender']); ?></td>
                <td class="td-age"><?php echo esc_html($animal['age']); ?></td>
                <td class="td-breed"><?php echo esc_html($animal['breed']); ?></pre></td>
                <td class="td-status"><a href="<?php echo esc_attr($this->get_animal_permalink($animal['id'])); ?>" target="_blank"><?php echo esc_html($animal['status']); ?></a></td>
                <td class="td-desc"><div><?php echo esc_html($animal['description']); ?></div></td>
            </tr>
<?php
endforeach;
?>
        </tbody>
    </table>
<?php
global $wp_object_cache;
$wp_object_cache->stats();
?>
</div>
<?php
    }
    /**
     * WP_Petfinder::plugins_dir()
     * Returns this plugin's dir. Deprecated.
     * @return string
     */
    public function plugins_dir()
    {
        return str_replace('/inc/', '/', plugin_dir_path(__file__));
    }
    /**
     * WP_Petfinder::plugins_url()
     * Returns this plugin's URL
     * @param mixed $file
     * @return
     */
    private function plugins_url($file)
    {
        return str_replace('/inc/', '/', plugins_url($file, __file__));
    }
    /**
     * WP_Petfinder::admin_menu()
     * Adds admin menu for this plugin
     * @return
     */
    public function admin_menu()
    {
        $app_icon = $this->get_option('plugin_icon', 'cat');
        add_menu_page('WP Petfinder', 'WP Petfinder', self::ROLE, 'wp-petfinder', [$this, 'wp_petfinder_page'], $this->plugins_url('images/icon-'. $app_icon. '-16.png'), 6);
    }
    /**
     * WP_Petfinder::get_animals()
     * Returns array with animals from API or cache (if exists).
     * More info: https://www.petfinder.com/developers/v2/docs/#get-animals
     * @param mixed $args
     * @return
     */
    public function get_animals($args = [], $expire = 0)
    {
        if ($expire == 0)
        {
            $expire = $this->animals_cache_expire;
        }
        $args['limit'] = $this->limit;
        $this->get_current_page();
        $args['page'] = $this->paged;
        $this->source = 'cache';
        $key = 'pf_animals_'. $this->array_hash($args);
        $key_p = $key . '_pagination';
        $animals = $this->cache_get($key);
        $this->pagination = $this->cache_get($key_p);
        if (!$animals)
        {
            $this->source = 'api';
            $response = $this->api->get_animals($args);
            if (isset($response['pagination']))
            {
                $this->pagination = $response['pagination'];
                $this->cache_set($key_p, $this->pagination, $expire);
            }
            if (isset($response['animals']))
            {
                $animals = $response['animals'];
                $this->cache_set($key, $animals, $expiry);
            }
        }
        if (!is_array($animals))
        {
            $animals = [];
        }
        foreach ($animals as $key => $animal)
        {
            $animals[$key] = $this->get_animal_data($animal);
        }
        return $animals;
    }
    /**
     * WP_Petfinder::get_animal_data()
     * Fills some Animal fields with data, e.g thumbnail, large image, etc
     * @param array $animal
     * @return array
     */
    private function get_animal_data($animal)
    {
        $return = $animal;
        if (is_array($animal['breeds']))
        {
            $return['breed'] = isset($animal['breeds']['primary']) ? $animal['breeds']['primary'] : 'unknown';
        }
        $type = trim(strtolower($animal['type']));
        if (!in_array($type, ['dog', 'cat']))
        {
            $type = 'default';
        }
        $placeholder = $this->plugins_url('images/placeholder-'. $type. '.png');
        $photo = @current($animal['photos']);
        if (is_array($photo))
        {
            $return['thumbnail'] = $photo['medium'];
            $return['image']     = $photo['full'];
        }
        else
        {
            $return['thumbnail'] = $placeholder;
            $return['image']     = $placeholder;
        }
        $return['color'] = isset($animal['colors']['primary']) ? $animal['colors']['primary'] : '';
        return $return;
    }
    /**
     * WP_Petfinder::get_animal()
     * Get Animal by id. More info: https://www.petfinder.com/developers/v2/docs/#get-animal
     * @param mixed $id
     * @return
     */
    public function get_animal($id, $expire = 0)
    {
        if ($expire == 0)
        {
            $expire = $this->animals_cache_expire;
        }
        $this->source = 'cache';
        $key = 'pf_animal_'. md5($id);
        if (isset($this->animal[$id]))
        {
            $animal = $this->animal[$id];
        }
        else
        {
            $animal = $this->cache_get($key);
            $this->animal[$id] = $animal;
        }
        if (!is_array($animal))
        {
            $this->source = 'api';
            $animal = $this->api->get_animal($id);
            $this->cache_set($key, $animal, $expire);
            $this->animal[$id] = $animal;
        }
        else
        {
            $animal['source'] = 'cache';
        }
        $animal['description'] = $this->get_description($animal['url'], $expire);
        $animal['description'] = apply_filters('wppf_description', $animal['description'], $id);
        $animal = $this->get_animal_data($animal);
        return $animal;
    }
    /**
     * WP_Petfinder::get_organizations()
     * Get Organizations. More info: https://www.petfinder.com/developers/v2/docs/#get-organizations
     * @param mixed $args
     * @return
     */
    public function get_organizations($args = [], $expire = 0)
    {
        if ($expire == 0)
        {
            $expire = $this->animals_cache_expire;
        }
        $this->source = 'cache';
        $this->get_current_page();
        $args['page'] = $this->paged;
        $args['limit'] = $this->limit;
        $key = 'pf_organizations_'. $this->array_hash($args);
        $key_p = $key . '_pagination';
        $organizations = $this->cache_get($key);
        $this->pagination = $this->cache_get($key_p);
        if (!is_array($organizations))
        {
            $this->source = 'api';
            $response = $this->api->get_organizations($args);
            if (isset($response['pagination']))
            {
                $this->pagination = $response['pagination'];
                $this->cache_set($key_p, $this->pagination, $expire);
            }
            if (isset($response['organizations']))
            {
                $organizations = $response['organizations'];
                $this->cache_set($key, $organizations, $expire);
            }
        }
        return $organizations;
    }
    /**
     * WP_Petfinder::get_organization()
     * Returns organization by id. More info: https://www.petfinder.com/developers/v2/docs/#get-organization
     * @param mixed $id
     * @return
     */
    public function get_organization($id, $expire = 0)
    {
        if ($expire == 0)
        {
            $expire = $this->animals_cache_expire;
        }
        $this->source = 'cache';
        $key = 'pf_organization_'. md5($id);
        $organization = $this->cache_get($key);
        if (!is_array($organization))
        {
            $this->source = 'api';
            $organization = $this->api->get_organization($id);
            $organization['source'] = 'api';
            $this->cache_set($key, $organization, $expire);
        }
        else
        {
            $organization['source'] = 'cache';
        }
        return $organization;
    }
    /**
     * WP_Petfinder::get_animal_types()
     * Returns array with animal types. More info: https://www.petfinder.com/developers/v2/docs/#get-animal-types
     * @return
     */
    public function get_animal_types($any = true, $expire = 0)
    {
        if ($expire == 0)
        {
            $expire = $this->animals_cache_expire;
        }
        $this->source = 'cache';
        if (isset($this->types))
        {
            $this->source = 'memory';
            return $this->types;
        }
        $types = json_decode(get_transient('pf_animal_types'), JSON_OBJECT_AS_ARRAY);
        if (!is_array($types))
        {
            $this->source = 'api';
            $types = $this->api->get_animal_types();
            $this->types = $types;
            set_transient('pf_animal_types', json_encode($types), $expire);
        }
        return $types;
    }
    /**
     * WP_Petfinder::get_animal_breeds()
     * Returns array with animal breeds of specific animal type. More info: https://www.petfinder.com/developers/v2/docs/#get-animal-breeds
     * @param string $type
     * @return
     */
    public function get_animal_breeds($type = 'dog', $expire = 0)
    {
        $type = strtolower($type);
        if ($expire == 0)
        {
            $expire = $this->animals_cache_expire;
        }
        $this->source = 'cache';
        $breeds = json_decode(get_transient('pf_animal_breeds_'. $type), JSON_OBJECT_AS_ARRAY);
        if (!is_array($breeds))
        {
            $this->source = 'api';
            $breeds = $this->api->get_animal_breeds($type);
            set_transient('pf_animal_breeds_'. $type, json_encode($breeds), $expire);
        }
        foreach ($breeds as $key=>$breed)
        {
            $breeds[$breed] = esc_attr($breed);
        }
        return $breeds;
    }
    /**
     * WP_Petfinder::get_animal_breeds_array()
     * Returns cat & dog breeds as array
     * @return array
     */
    function get_animal_breeds_array()
    {
        $this->breeds = [];
        $this->breeds['cat'] = $this->get_animal_breeds('cat');
        $this->breeds['dog'] = $this->get_animal_breeds('dog');
        return $this->breeds;
    }
    /**
     * WP_Petfinder::plugins_loaded()
     * Earliest WP hook to get plugin options
     * @return
     */
    public function plugins_loaded()
    {
        $this->load_options();
    }
    /**
     * WP_Petfinder::load_options()
     * Get plugin options from wp_options table and store them in $this->options
     * @return boolean
     */
    private function load_options()
    {
        if (!is_array($this->options) || count($this->options) == 0)
        {
            $this->options    = get_option('wppf_options', []);
            return true;
        }
        return false;
    }
    /**
     * WP_Petfinder::get_option()
     * Returns this plugin's option
     * @param mixed $name
     * @param string $default
     * @return
     */
    public function get_option($name, $default = '')
    {
        $this->load_options();
        return !empty($this->options[$name]) ? $this->options[$name] : $default;
    }
    /**
     * WP_Petfinder::init()
     * init() hook
     * @return
     */
    public function init()
    {
        // add rewrite rule for animal details
        /*
        $page = intval($this->get_option('animal_page', 0));
        if ($page > 0 && $this->permalinks_active())
        {
            $tmp = explode('/', get_permalink($page));
            while (end($tmp) == '')
            {
                array_pop($tmp);
            }
            $perm = end($tmp);
            if ($perm)
            {
                add_rewrite_rule('^('. $perm. ')/([^/]*)/?', 'index.php?pagename=$matches[1]&id=$matches[2]', 'top');
                add_filter('query_vars', function($vars)
                {
                    $vars[] = 'id';
		          return $vars;
                });
            }
        }
        */
        if (current_user_can(self::ROLE))
        {
            $plugin_options = require WPPF_DIR . '/inc/option.php';
            $theme_options = new \VP_Option(
            [
                'is_dev_mode'           => false,
                'option_key'            => 'wppf_options',
                'page_slug'             => 'wppf_options',
                'template'              => $plugin_options,
                //'menu_page'             => 'options-general.php',
                'menu_page'             => 'wp-petfinder',
                'use_auto_group_naming' => true,
                'use_util_menu'         => false,
                'minimum_role'          => self::ROLE,
                'layout'                => 'fluid',
                'page_title'            => __('Options', 'wppf'), // page title
                'menu_label'            => __('Options', 'wppf'), // menu label
            ]);
        }
    }
    /**
     * WP_Petfinder::get_description()
     * Get Animal description (full)
     * @param string $url
     * @param integer $expiry
     * @return string
     */
    public function get_description($url, $expiry = 0)
    {
        if ($expiry == 0)
        {
            $expiry = $this->animals_cache_expire;
        }
        $key = 'pf_description_'. md5($url);
        $desc = $this->cache_get($key);
        $this->source = 'cache';
        if (!$desc)
        {
            $this->source = 'api';
            $desc = $this->api->get_description($url);
            $this->cache_set($key, $desc, $expiry);
        }
        return nl2br(trim(str_replace('&#039;', "'", html_entity_decode($desc))));
    }
    /**
     * WP_Petfinder::comma_separated()
     *
     * @param mixed $str
     * @return
     */
    function comma_separated($str)
    {
        $values = [];
        $tmp = explode(',', $str);
        if (is_array($tmp))
        {
            foreach ($tmp as $t)
            {
                if (trim($t))
                {
                    $values[] = trim($t);
                }
            }
        }
        return $values;
    }
    /**
     * WP_Petfinder::get_animal_gender_array()
     *
     * @return
     */
    function get_animal_gender_array()
    {
        return ['' => __('Both', 'wppf'), 'male' => __('Male', 'wppf'), 'female' => __('Female', 'wppf')];
    }
    /**
     * WP_Petfinder::animal_gender_options()
     * Prints gender options in search form
     * @return void
     */
    public function animal_gender_options()
    {
        $genders = $this->get_animal_gender_array();
        foreach ($genders as $key => $value)
        {
            // sanitize
            $sel = (sanitize_text_field($_GET['gender']) == $key) ? ' selected="selected"' : '';
            echo '<option value="', esc_attr($key), '"', $sel, '>', esc_html($value), '</option>';
        }
    }
    /**
     * WP_Petfinder::animal_type_options()
     * Prints animal type options in search form
     * @return void
     */
    public function animal_type_options()
    {
        // sanitize
        $t = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'dog';
        $types = wppf()->get_animal_types();
        foreach ($types as $type)
        {
            $sel = (strtolower($type['name']) == $t) ? ' selected="selected"' : '';
?>
<option value="<?php echo esc_attr(strtolower($type['name'])); ?>"<?php echo $sel; ?>><?php echo esc_html($type['name']); ?></option>
<?php
        }
    }
    /**
     * WP_Petfinder::document_title_parts()
     * Document title filter - fill it with animal information
     * @param array $title
     * @return array
     */
    function document_title_parts($title)
    {
        if ($this->is_animal_page())
        {
            $animal = $this->get_animal($this->get_animal_id());
            $pattern = $this->get_option('page_title', '[name] - [gender] [age] [type]');
            /*
            d($animal);
            d($pattern);
            die();
            */
            $regex = "/\[.*?\]/";
            preg_match_all($regex, $pattern, $matches);
            if (is_array($matches))
            {
                $match = current($matches);
                foreach ($match as $val)
                {
                    $name = trim(str_replace(['[', ']'], '', $val));
                    $value = '';
                    if (isset($animal[$name]))
                    {
                        $value = $animal[$name]. ' ';
                    }
                    $pattern = str_replace('['. $name. ']', $value, $pattern);
                }
            }
            $pattern = trim($pattern);
            $title['title'] = $pattern;
            $this->page_title = $pattern;
            unset($title['page']);
        }
        return $title;
    }
    /**
     * WP_Petfinder::is_animal_page()
     * Returns true if current page = Animal details
     * @return
     */
    private function is_animal_page()
    {
        global $post;
        $ap_id = $this->get_option('animal_page', 1);
        $page_id = $post->ID;
        return $ap_id == $page_id;
    }
    /**
     * WP_Petfinder::the_content()
     * the_content filter
     * @param mixed $content
     * @return
     */
    public function the_content($content)
    {
        return $content;
    }
    /**
     * WP_Petfinder::get_animal_id()
     * Returns Animal id from Animal detail page
     * @return integer
     */
    private function get_animal_id()
    {
        global $wp;
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0; // sanitize intval
        $id = isset($_GET['animal_id']) ? intval($_GET['animal_id']) : $id; // sanitize intval
        if ($this->permalinks_active())
        {
            $newid = isset($wp->query_vars['page']) ? intval($wp->query_vars['page']) : 0; // sanitize intval
        }
        if ($newid > 0)
        {
            $id = $newid;
        }
        return $id;
    }
    /**
     * WP_Petfinder::pf_animal()
     * Shortcode to display a field from animal record, specified in 'display' attribute
     * @param array $atts
     * @return string
     */
    public function pf_animal($atts)
    {
        global $animal;
        ob_start();
        $id = $this->get_animal_id();
        $id = isset($atts['id']) ? intavl($atts['id']) : $id;
        if ($id > 0)
        {
            $animal = $this->get_animal($id);
            if (is_numeric($animal['id']))
            {
                if (isset($atts['display']))
                {
                    $var = $atts['display'];
                    echo esc_html($animal[$var]);
                }
            }
            else
            {
                $this->invalid_id($id);
            }
        }
        else
        {
            $this->invalid_id($id);
        }
        return ob_get_clean();
    }
    //
    private function invalid_id($id)
    {
        echo '<p class="error">', __(sprintf('Invalid Animal ID: %d', $id), 'wppf'), '</p>';
        return true;
    }
    /**
     * WP_Petfinder::pf_animal()
     * Animal Details shortcode
     * @param mixed $atts
     * @return
     */
    public function pf_details($atts)
    {
        ob_start();
        //return ob_get_clean();
        $id = $this->get_animal_id();
        if (isset($atts['id']))
        {
            $id = intval($atts['id']);
        }
        if ($id > 0)
        {
            $animal = $this->get_animal($id);
            if (is_numeric($animal['id']))
            {
                require_once $this->locate_template('animal-details.php');
            }
            else
            {
                $this->invalid_id($id);
            }
        }
        else
        {
            $this->invalid_id($id);
        }
        return ob_get_clean();
    }
    /**
     * WP_Petfinder::pf_search_form()
     * Shortcode [pf_search_form]
     * @param array $atts
     * @return
     */
    public function pf_search_form($atts)
    {
        ob_start();
        if (!isset($_GET['location']))
        {
            $_GET['location'] = isset($atts['location']) ? sanitize_text_field($atts['location']) : '';
        }
        if (!isset($_GET['type']))
        {
            $_GET['type'] = isset($atts['type']) ? sanitize_text_field($atts['type']) : '';
        }
        if (!isset($_GET['gender']))
        {
            $_GET['gender'] = isset($atts['gender']) ? sanitize_text_field($atts['gender']) : '';
        }
        foreach ($_GET as $key => $value)
        {
            if ($value == '')
            {
                unset($_GET[$key]);
            }
        }
        require_once $this->locate_template('search-form.php');
        return ob_get_clean();
    }
    /**
     * WP_Petfinder::print_array()
     *
     * @param array $arr
     * @return string
     */
    function print_array($arr)
    {
        $s = '';
        if (is_array($arr))
        {
            foreach ($arr as $key => $value)
            {
                $key = ucwords(str_replace('_', ' ', $key));
                $span = 'unknown';
                $span = ($value == true) ? 'checked' : $span;
                $span = ($value == false) ? 'unchecked' : $span;
                $s .= '<span class="wppf-icon icon-'. $span. '"></span>&nbsp;'. $key. ' &nbsp; ';
            }
        }
        $s = substr($s, 0, strlen($s)-2);
        return $s;
    }
    /**
     * WP_Petfinder::pf_search_results()
     * Shortcode [pf_search_results]
     * @param array $atts
     * @return
     */
    public function pf_search_results($atts)
    {
        ob_start();
        $args = [];
        $args['location']     = isset($atts['dlocation']) ? $atts['dlocation'] : '';
        $args['location']     = isset($_GET['location']) ? sanitize_text_field($_GET['location']) : $args['location'];
        $args['location']     = isset($atts['location']) ? $atts['location'] : $args['location'];
        $args['type']         = isset($atts['dtype']) ? $atts['dtype'] : '';
        $args['type']         = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : $args['type'];
        $args['type']         = isset($atts['type']) ? $atts['type'] : $args['type'];
        $args['gender']       = isset($atts['dgender']) ? $atts['dgender'] : '';
        $args['gender']       = isset($_GET['gender']) ? sanitize_text_field($_GET['gender']) : $args['gender'];
        $args['gender']       = isset($atts['gender']) ? $atts['gender'] : $args['gender'];
        $args['organization'] = isset($_GET['shelter_id']) ? sanitize_text_field($_GET['shelter_id']) : '';
        $args['organization'] = isset($atts['shelter_id']) ? $atts['shelter_id'] : $args['shelter_id'];
        //$args['status'] = isset($atts['status']) ? $atts['status'] : isset($_GET['status']) ? $_GET['status'] : '';
        $args['size']         = isset($_GET['size']) ? sanitize_text_field($_GET['size']) : '';
        $args['size']         = isset($atts['size']) ? $atts['size'] : $args['size'];
        $args['view']         = isset($atts['dview']) ? $atts['dview'] : 'grid';
        $args['view']         = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : $args['view'];
        $args['view']         = isset($atts['view']) ? $atts['view'] : $args['view'];
        $args['status']       = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $args['status']       = isset($atts['status']) ? $atts['status'] : $args['status'];
        $args['distance']     = isset($atts['distance']) ? intval($atts['distance']) : '';
        $args['sort']         = isset($atts['sort']) ? $atts['sort'] : '';
        $args['name']         = isset($_GET['animal_name']) ? sanitize_text_field($_GET['animal_name']) : '';
        $_GET['view']         = $args['view'];
        $this->limit          = isset($atts['limit']) ? intval($atts['limit']) : $this->limit;
        $this->view           = $args['view'];
        if (isset($atts['view']))
        {
            $this->view_only = true;
        }
        unset($args['view']);
        foreach ($args as $key => $value)
        {
            if ($value == '')
            {
                unset($args[$key]);
            }
        }
        // If shelter_id is specified, location is set automatically
        if (isset($args['organization']))
        {
            unset($args['location']);
        }
        //d($args);
        $animals = $this->get_animals($args);
        if (!is_array($animals))
        {
            $animals = [];
        }
        if (count($animals) > 0)
        {
            require_once $this->locate_template('search-results.php');
        }
        else
        {
            require_once $this->locate_template('search-results-empty.php');
        }
        if (self::DEBUG == true) echo '<!-- Source: ', $this->source, ' -->';
        return ob_get_clean();
?>
<?php
    }
    /**
     * WP_Petfinder::locate_template()
     * Try to locate template in current theme. If not found, then in current-plugins-dir/templates/
     * @param string $template
     * @return string
     */
    function locate_template($template)
    {
        $theme = get_stylesheet_directory(). '/wp-petfinder/'. $template;
        if (file_exists($theme))
        {
            return $theme;
        }
        $plugin = WPPF_DIR. '/templates/'. $template;
        return $plugin;
    }
    /**
     * WP_Petfinder::winput()
     * Widget's input field
     * @param array $args
     * @return
     */
    public function winput($args)
    {
        if ($args['tooltip'])
        {
            $tooltip = ' <sup><a href="#" title="'. esc_attr($args['tooltip']). '">?</a></sup>';
        }
?>
<p>
    <label for="<?php echo esc_attr($args['id']); ?>"><?php echo esc_html($args['title']), $tooltip; ?></label>
    <input class="widefat" id="<?php echo esc_attr($args['id']); ?>" name="<?php echo esc_attr($args['name']); ?>" type="text" value="<?php echo esc_attr($args['value']); ?>" />
</p>
<?php
    }
    /**
     * WP_Petfinder::wselect()
     * Widget's select field
     * @param array $args
     * @return
     */
    public function wselect($args, $class='')
    {
        if ($args['tooltip'])
        {
            $tooltip = ' <sup><a href="#" title="'. esc_attr($args['tooltip']). '">?</a></sup>';
        }
        //$cl = isset($args['class']) ? $args['class'] : '';
        //echo '<pre>'; print_r($cl); echo '</pre>';
?>
<p>
    <label for="<?php echo esc_attr($args['id']); ?>"><?php echo esc_html($args['title']), $tooltip; ?></label>
    <select class="widefat <?php echo esc_attr($class); ?>" id="<?php echo esc_attr($args['id']); ?>" name="<?php echo esc_attr($args['name']); ?>" value="<?php echo esc_attr($args['value']); ?>">
<?php
        if (is_array($args['choices']))
        {
            foreach ($args['choices'] as $k => $v)
            {
                $sel = ($k == $args['value']) ? ' selected="selected"' : '';
                echo '<option value="', esc_attr($k), '"', $sel, '>', esc_html($v), '</option>';
            }
        }
?>
    </select>
</p>
<?php
    }
}
?>