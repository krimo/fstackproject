
    /**
     * Actually display the new submission on screen
     */
    function displaySubmission(names, attending, howmany, id) {
        var content,
        contentAtt = '<div class="panel panel-primary animated" data-submission-id="'+id+'"><div class="panel-heading"><h3 class="panel-title">'+ names +'</h3></div><div class="panel-body">Is attending with <b>'+ howmany +'</b> guests.</div></div>',
        contentNot = '<div class="panel panel-danger animated" data-submission-id="'+id+'"><div class="panel-heading"><h3 class="panel-title">' + names + '</h3></div><div class="panel-body">Is <b>not</b> attending.</div></div>';

        (attending === '1') ? content = contentAtt : content = contentNot;

        $('#attendance').prepend(content);

        // $('#attendance')[0].scrollTop = $('#attendance')[0].scrollHeight;
    }

    // function deleteSubmission (id) {
    //     var theId = id;
    //     $.ajax({
    //             url: 'server/interface.php',
    //             type: 'DELETE',
    //             data: {
    //                 id: theId
    //             },
    //             success: function () {
    //                 $el = $('#attendance').find('[data-submission-id='+theId+']');
    //                 $el.remove();
    //             }
    //         });
    // }

    /**
     * Update the attendees list with changes only
     */
    function updateView (data) {
        var $attendees = $('#attendance > .panel'),
        attendeesIds = [];

        $.each( $attendees, function() {
            attendeesIds.push($(this).attr('data-submission-id'));
        });

        $.each( data, function() {
            var theId = this.id,
            that = this;

            if (attendeesIds.indexOf(theId) === -1) {
                displaySubmission(that.names, that.attending, that.howmany, theId);
            }
        });
    }


    /**
     * Long Polling (cf. https://github.com/panique/php-long-polling)
     */
     function getContent(timestamp)
     {
        var queryString = {'timestamp' : timestamp};

        $.ajax(
        {
            type: 'GET',
            url: 'server/polling.php',
            data: queryString,
            success: function(data){
                    // put result data into "obj"
                    var obj = jQuery.parseJSON(data);
                    // put the data into the view
                    updateView(jQuery.parseJSON(obj.data_from_file));
                    // call the function again, this time with the timestamp we just got from server.php
                    getContent(obj.timestamp);
                }
            }
            );
     }

    // DOM Ready
    $(function() {
        $('#rsvpform').on('submit', function (e) {
            e.preventDefault();

            var $that = $(this),
                theFormData = $(this).serialize();

            $that.find('[type=submit]').attr('disabled', 'disabled').text('Submitting...');

            $.ajax({
                url: 'server/interface.php',
                type: 'post',
                data: theFormData,
                success: function (data) {
                    $(data).appendTo($('#alerts'));
                    $that.find('[type=submit]').attr('disabled', false).text('Submit');
                }
            });
        });

        getContent();
    });
