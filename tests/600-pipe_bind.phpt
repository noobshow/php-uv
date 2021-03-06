--TEST--
Check for pipe bind
--FILE--
<?php
define("PIPE_PATH", dirname(__FILE__) . "/pipe_test.sock");
@unlink(PIPE_PATH);
$a = uv_pipe_init(uv_default_loop(), 0);
$ret = uv_pipe_bind($a, PIPE_PATH);

uv_listen($a, 8192, function($stream) {
    $pipe = uv_pipe_init(uv_default_loop(), 0);
    uv_accept($stream, $pipe);
    uv_read_start($pipe,function($pipe, $nread, $buffer) use ($stream) {
        if ($nread === \UV::EOF) {
            return;
        }

        echo $buffer;
        uv_read_stop($pipe);
        uv_close($stream, function() {
            @unlink(PIPE_PATH);
        });
    });
});

$b = uv_pipe_init(uv_default_loop(), 0);
uv_pipe_connect($b, PIPE_PATH, function($a, $b) {
    uv_write($b, "Hello", function($stream, $stat) {
        uv_close($stream);
    });
});

uv_run();
exit;
--EXPECT--
Hello
