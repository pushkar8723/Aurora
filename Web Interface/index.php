<?php
require_once 'config.php';
require_once 'components.php';
$_SESSION['url'] = $_SERVER['REQUEST_URI']; // used by process.php to send to last visited page
$query = "select value from admin where variable='mode'";
$judge = DB::findOneFromQuery($query);
if ($judge['value'] == 'Lockdown' && isset($_SESSION['loggedin']) && !isAdmin()) {
    session_destroy();
    session_regenerate_id(true);
    session_start();
    $_SESSION['msg'] = "Judge is in Lockdown mode and so you have been logged out.";
    redirectTo(SITE_URL . "/");
}
doCompetitionCheck(); //Activate competition when planned
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <link type="text/css" rel="stylesheet" href="<?php echo SITE_URL ?>/css/bootstrap.css" media="screen" />
        <link type="text/css" rel="stylesheet" href="<?php echo SITE_URL ?>/css/style.css" media="screen" />
        <script type="text/javascript" src="<?php echo SITE_URL ?>/js/jquery-3.1.0.min.js"></script>
        <script type="text/javascript" src="<?php echo SITE_URL ?>/js/bootstrap.js"></script>
        <script type="text/javascript" src="<?php echo SITE_URL ?>/js/plugin.js"></script>
        <script type="text/javascript">
            $(window).load(function() {
                if ($('#sidebar').height() < $('#mainbar').height())
                    $('#sidebar').height($('#mainbar').height());
            });
        </script>
        <title>Aurora</title>
        <link rel='shortcut icon' href='<?php echo SITE_URL; ?>/img/favicon.png' />
    </head>
    <body>
        <?php if ($judge['value'] == 'Active' && isset($_SESSION['loggedin'])) { ?>
            <script type='text/javascript'>
                function settitle() {
                    var t = window.document.title;
                    var n = t.match(/(\d*)\)/gi);
                    console.log(n);
                    if (n != null) {
                        n = parseInt(n) + 1;
                    } else {
                        n = 1;
                    }
                    window.document.title = "(" + n + ") Aurora";
                }
                function resettile() {
                    $.ajax({
                        type: "GET",
                        url: "<?php echo SITE_URL; ?>/broadcast.php",
                        data: {updatetime: ""}
                    });
                    window.document.title = "Aurora";
                }
                window.setTimeout("bchk();", <?php echo rand(300000, 600000); ?>);
                $.ajax("<?php echo SITE_URL; ?>/broadcast.php").done(function(msg) {
                    var json = eval('(' + msg + ')');
                    console.log(msg);
                    if (json.broadcast.length != 0) {
                        var str, i;
                        str = "";
                        for (i = 0; i < json.broadcast.length; i++)
                            str += "<b>" + json.broadcast[i].title + ":</b><br/>" + json.broadcast[i].msg + "<br/><br/>";
                        $("#bmsg").html(str);
                        $('#myModal').on('hidden.bs.modal', function() {
                            resettile();
                        });
                        $("#myModal").modal('show');
                        settitle();
                    }
                });
                function bchk() {
                    $.ajax("<?php echo SITE_URL; ?>/broadcast.php").done(function(msg) {
                        var json = eval('(' + msg + ')');
                        console.log(msg);
                        if (json.broadcast.length != 0) {
                            var str, i;
                            str = "";
                            for (i = 0; i < json.broadcast.length; i++)
                                str += "<b>" + json.broadcast[i].title + ":</b><br/>" + json.broadcast[i].msg + "<br/><br/>";
                            $("#bmsg").html(str);
                            $('#myModal').on('hidden.bs.modal', function() {
                                resettile();
                            });
                            $("#myModal").modal('show');
                            settitle();
                        }
                    });
                    window.setTimeout("bchk();", 600000);
                }
            </script>
            <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title" id="myModalLabel">Alert</h4>
                        </div>
                        <div class="modal-body" id="bmsg">

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div>
        <?php }
        ?>
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse-1" aria-expanded="false">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="<?php echo SITE_URL; ?>/">Aurora</a>
                </div>

                <div class="collapse navbar-collapse" id="navbar-collapse-1">
                    <ul class="nav navbar-nav">
                        <!-- <li><a href="<?php echo SITE_URL; ?>/home">Home</a></li> -->
                        <li><a href="<?php echo SITE_URL; ?>/problems">Problems</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/contests">Contests</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/rankings">Rankings</a></li>
                        <li><a href="<?php echo SITE_URL; ?>/submissions">Submissions</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Help <span class="caret"></span></a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="<?php echo SITE_URL; ?>/faq">FAQ</a></li>
                                <li><a href="<?php echo SITE_URL; ?>/contact">Contact Us</a></li>
                            </ul>
                        </li>
                    </ul>
                    <?php if (isset($_SESSION['loggedin'])) { ?>
                        <ul class="nav navbar-nav pull-right">
                            <?php if ($_SESSION['team']['status'] == 'Admin') { ?>
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                        Admin
                                        <b class="caret"></b>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a href='<?php echo SITE_URL; ?>/adminjudge'>Judge Settings</a></li>
                                        <li><a href='<?php echo SITE_URL; ?>/adminproblem'>Problem Settings</a></li>
                                        <li><a href='<?php echo SITE_URL; ?>/admincontest'>Contest Settings</a></li>
                                        <li><a href='<?php echo SITE_URL; ?>/adminteam'>Team Settings</a></li>
                                        <li><a href='<?php echo SITE_URL; ?>/admingroup'>Group Settings</a></li>
                                        <li><a href='<?php echo SITE_URL; ?>/adminclar'>Clarifications</a></li>
                                        <li><a href='<?php echo SITE_URL; ?>/adminbroadcast'>Broadcast</a></li>
                                        <li><a href='<?php echo SITE_URL; ?>/adminlog'>Request Logs</a></li>                                            
                                    </ul>
                                </li>                              
                            <?php } ?>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                    Account
                                    <b class="caret"></b>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href='<?php echo SITE_URL; ?>/edit'>Account Settings</a></li>
                                    <li><a href='<?php echo SITE_URL; ?>/process.php?logout'>Logout</a></li>
                                </ul>
                            </li>
                        </ul>
                    <?php } ?>
                </div>
            </div> 
        </nav> 
        <div class="container bodycont">
            <div class='row'>
                <div class='col-md-9' id='mainbar'>
                    <?php if (isset($_SESSION['msg']) && $_SESSION['msg'] != "") { ?>
                        <div class="alert alert-info" style="margin-top: 20px;">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <div class="text-center"><?php
                                echo $_SESSION['msg'];
                                unset($_SESSION['msg']);
                                ?></div>
                        </div>
                        <?php
                    }
                    if (!isset($_GET['tab']) || $_GET['tab'] == 'home') {
                        $str = 'files/home.php';
                    } else {
                        $str = 'files/' . $_GET['tab'] . '.php';
                    }
                    if (file_exists($str))
                        require $str;
                    else
                        echo "<br/><br/><br/><div style='padding: 10px;'><h1>Page not Found :(</h1>The page you are searching for is not on this site.</div><br/><br/><br/>";
                    ?>
                </div>
                <div class='col-md-3'>
                    <!-- Login Panel -->
                    <div class="panel panel-default">
                        <?php loginbox(); ?>
                    </div>
                    <!-- ./Login Panel -->

                    <!-- Contest Panel -->
                    <div class="panel panel-default">
                        <div class="panel-heading text-center">
                            <h3 class="panel-title">Contest</h3>
                        </div>
                        <div class="panel-body text-center">
                            <?php contest_status(); ?>
                        </div>
                    </div>
                    <!-- ./Contest Panel -->

                    <!-- Ranking Panel -->
                    <div class="panel panel-default">

                    <?php if ($judge['value'] == 'Active') { ?>
                        <!-- Contest Ranking -->
                        <div class="panel-heading text-center">
                            <h3 class="panel-title">Contest Ranking</h3>
                        </div>
                        <div class="panel-body text-center">
                            <div id="live-ranking">
                            <?php getCurrentContestRanking(); ?>
                                <a style="float:right;" href="<?php echo SITE_URL.'/rank/'.getCurrentContest(); ?>">View all</a>
                            </div>
                        </div>

                    <?php } else { ?>
                        <!-- Overall Ranking -->
                        <div class="panel-heading text-center">
                            <h3 class="panel-title">Overall Rankings</h3>
                        </div>
                        <div class="panel-body text-center">
                            <?php rankings(); ?>
                        </div>
                    <?php } ?>
                    </div>
                    <!-- ./Ranking Panel -->

                    <?php
                    /* My Submissions Panel */
                    if (isset($_SESSION['loggedin'])) mysubs();
                    /* Latest Submissions Panel */
                    if ($judge['value'] == 'Active') latestsubs();
                    ?>

                </div>
            </div>
        </div>
        <div class="footer">
            <a href="https://github.com/pushkar8723/Aurora" target="_blank">Aurora - An Open Source Online Judge</a>
        </div>
    </body>
</html>
