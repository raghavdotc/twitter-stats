
<?php
if (isset($response)) :
    echo $response;
else :
    ?>
    Welcome to My App!
    <script>
        window.last_tweet_id = true;
        var make_async_request = function() {
            var url = '/twitter/get_user_timeline_before_id';
            if (window.last_tweet_id !== true && window.last_tweet_id !== false) {
                url += '/' + window.last_tweet_id;
            }
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'JSON',
                success: function(result) {
                    window.last_tweet_id = result.last_tweet_id;
                    if (window.last_tweet_id !== false) {
                        make_async_request();
                    } else {
                        $("#content").append("<a href='/twitter/see_statistics'>Click here to see statictis</a>");
                    }
                },
                error: function(error) {
                },
                complete: function() {

                }
            });
        }

        $(function() {
            make_async_request();
        })
    </script>
<?php endif;
?>