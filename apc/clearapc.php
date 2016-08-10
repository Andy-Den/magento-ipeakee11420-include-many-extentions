<?php
try {
    apc_clear_cache();
    apc_clear_cache('user');
    apc_clear_cache('opcode');
    echo "APC cache cleared";
} catch (Exception $e) {
    echo $e;
}
?>