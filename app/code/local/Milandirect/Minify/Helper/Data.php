<?php

/**
 * Rewrite Helper to use minify plus
 *
 * @category  Milandirect
 * @package   Milandirect_Minify
 * @author    Balance Internet Team <dev@balanceinternet.com.au>
 * @copyright 2015 Balance
 */
class Milandirect_Minify_Helper_Data extends Apptrian_Minify_Helper_Data
{
    /**
     * Array of paths that will be scaned for css and js files.
     *
     * @var array
     */
    protected $paths = null;

    /**
     * Minifies CSS and JS files.
     *
     * @return void
     */
    public function process()
    {
        // Get remove important comments option
        $removeComments = (int) Mage::getConfig()->getNode('apptrian_minify/minify_css_js/remove_comments', 'default');

        foreach ($this->getPaths() as $path) {

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::FOLLOW_SYMLINKS)
            );

            foreach ($iterator as $filename => $file) {

                if ($file->isFile() && preg_match('/^.+\.(css|js)$/i', $file->getFilename())) {

                    $filePath = $file->getRealPath();
                    if (!is_writable($filePath)) {
                        Mage::log('Minification failed for ' . $filePath . ' File is not writable.');
                        continue;
                    }

                    $ext         = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                    $optimized   = '';
                    $unoptimized = file_get_contents($filePath);

                    // If it is 0 byte file or cannot be read
                    if (!$unoptimized) {
                        Mage::log('File ' . $filePath . ' cannot be read.');
                        continue;
                    }

                    // CSS files
                    if ($ext == 'css') {

                        if ($removeComments == 1) {

                            $optimized = Minify_CSS::minify($unoptimized, array('preserveComments' => false));

                        } else {

                            $optimized = Minify_CSS::minify($unoptimized);

                        }

                        // JS files
                    } else {

                        if ($removeComments == 1) {

                            $optimized = JSMinPlus::minify($unoptimized,'');

                        } else {

                            $optimized = JSMin::minify($unoptimized);

                        }

                    }

                    // If optimization failed
                    if (!$optimized) {
                        Mage::log('File ' . $filePath . ' was not minified.');
                        continue;
                    }


                    if (file_put_contents($filePath, $optimized, LOCK_EX) === false) {

                        Mage::log('Minification failed for ' . $filePath);

                    }

                }

            }

        }

    }
}