<style>
    .tweet {
        border-bottom: 1px solid #d2d2d2;
        margin-bottom: 10px;
    }

    .main-containers {
        height: 500px;
        overflow-y: scroll;
        margin-right: 20px;
    }

    #tweets-container {
        width: 400px;
    }

    #messenger-success {
        color: green;
    }

    #messenger-failure {
        color: green;
    }

</style>
<div class='main-containers' style='display: inline-block; vertical-align: top;'>
    <h2>Tweets</h2>
    <div id='tweets-container'>
        <?php foreach ($tweets as $tweet) : ?>
            <div class='tweet'>
                <div>
                    <h4>@<?php echo $tweet['User']['handle']; ?></h4>
                </div>
                <div>
                    <span><?php echo $tweet['Tweet']['tweet']; ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div>
        <button id='next-50' url='/twitter/fetch_next_50?offset=<?php echo $tweet['Tweet']['id']; ?>'>Next 50</button>
    </div>
</div>
<div class='main-containers' style='display: inline-block; vertical-align: top;'>
    <h2>Tweet count(DESC)</h2>
    <?php if ($tweet_count !== false) : ?>
        <table>
            <thead><tr><td>Tweet Count</td><td>User</td></tr></thead>
            <tbody>
                <?php foreach ($tweet_count as $tweet_user) : ?>
                    <tr><td style='text-align: center;'><?php echo $tweet_user[0]['number_tweets']; ?></td><td><?php echo $tweet_user['User']['handle']; ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<div class='main-containers' style='display: inline-block; vertical-align: top;'>
    <h2>Most tweeted URL domains</h2>
    <?php if ($domain_count !== false) : ?>
        <table>
            <thead><tr><td>Count</td><td>Domain</td></tr></thead>
            <tbody>
                <?php foreach ($domain_count as $domain) : ?>
                    <tr><td style='text-align: center;'><?php echo $domain[0]['number_tweets']; ?></td><td><?php echo $domain['TweetedUrl']['domain']; ?></td></tr>
                        <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<script>

    var render_tweets = function(tweets) {
        var str = "<div>";
        for (var i in tweets) {
            str += "<div class='tweet'>";
            str += "<div>";
            str += "<h4>@" + tweets[i].User.handle + "</h4>";
            str += "</div>";
            str += "<div>";
            str += "<span>" + tweets[i].Tweet.tweet + "</span>";
            str += "</div>";
            str += "</div>";
        }
        return str;
    }

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
        $(document).on("click", '#next-50', function(event) {
            $(".messenger").remove();
            $(event.target).prop('disabled', true);
            var url = $(event.target).attr("url");
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'JSON',
                success: function(result) {
                    if (result.status) {
                        $("#tweets-container").append(render_tweets(result.tweets));
                        $(event.target).prop('disabled', false);
                        $("#tweets-container").scrollTop($("#tweets-container").outerHeight());
                        $(event.target).after("<span class='messenger' id='messenger-success'>Tweets refreshed!!!!</span>");
                    } else {
                        $(event.target).after("<span class='messenger' id='messenger-failure'>no tweets retrieved!</span>");
                    }
                },
                error: function(error) {
                    alert(error.responseText);
                }
            });
        });
        make_async_request();
    })
</script>