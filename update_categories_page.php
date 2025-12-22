<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Ticket Categories - MONICOMLAB</title>
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
</head>
<body style="background: #f8f9fc; padding: 2rem;">
    <div class="container">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-tools"></i> Update Ticket Categories
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <p class="text-muted">
                        This script will update existing tickets to have proper categories (HARDWARE, SOFTWARE, NETWORK) 
                        based on their issue types. This is needed to ensure proper categorization in the troubleshooting dashboard.
                    </p>
                </div>
                
                <button id="runUpdate" class="btn btn-primary">
                    <i class="fas fa-play"></i> Run Category Update
                </button>
                
                <div id="results" class="mt-4" style="display: none;">
                    <div class="alert alert-info">
                        <i class="fas fa-spinner fa-spin"></i> Processing...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script>
        $('#runUpdate').click(function() {
            $('#results').show().html('<div class="alert alert-info"><i class="fas fa-spinner fa-spin"></i> Processing...</div>');
            
            $.get('update_ticket_categories.php', function(data) {
                $('#results').html('<div class="alert alert-success"><i class="fas fa-check"></i> Update Complete!</div><div class="mt-2">' + data + '</div>');
            }).fail(function() {
                $('#results').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Error running update script.</div>');
            });
        });
    </script>
</body>
</html>