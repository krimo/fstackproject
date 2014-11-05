<?php

require_once('FormstackApi.php');

$FORMSTACK = new FormstackApi('9e8f8f3cb87f9e7ec0f07d52743ec60c');
$dataFileContent = file_get_contents('data.json');

/**
*
*/
class ClassName extends AnotherClass
{

  function __construct(argument)
  {
    # code...
  }
}

/**
 * We first check if the data.json file is empty, if so we fill it with Formstack submissions
 */
if (sizeof(json_decode($dataFileContent, true)) === 0) {

    if ($verb === 'POST' && isset($_POST) && !empty($_POST)) {
        postSubmission($FORMSTACK);
    }

    $finalArray = array();
    $submissions = $FORMSTACK->request('/form/1864948/submission', 'GET', array());
    $s = json_decode($submissions, true);

    foreach ($s['submissions'] as $submission) {
        $response = json_decode($FORMSTACK->request('/submission/'.$submission['id'].'', 'GET'), true);

        $resultArray = array();

        $resultArray['id'] = $submission['id'];
        $resultArray['names'] = $response['data'][0]['value'];
        $resultArray['attending'] = $response['data'][1]['value'];
        $resultArray['howmany'] = $response['data'][2]['value'];

        array_push($finalArray, $resultArray);
    }

    file_put_contents('data.json', json_encode($finalArray));

}

/**
 * Helper function to post to the Formstack form
 * @param  Object $formstack An instance of the FormstackAPI class
 * @return Array            The formatted array containing the post data
 */
function postSubmission($formstack) {

    $arr = array();

    $formData = array(
        'field_28492294' => $_POST['names'],
        'field_28492295' => $_POST['attending'],
        'field_28492296' => $_POST['howmany']
    );

    $formstackResponse = json_decode($formstack->request('/form/1864948/submission', 'POST', $formData), true);

    array_push($arr, array(
        'id' => $formstackResponse['id'],
        'names' => $formData['field_28492294'],
        'attending' => $formData['field_28492295'],
        'howmany' => $formData['field_28492296'],
    ));

    return $arr;
}

/**
 * A function to update the data.json file whenever something is posted
 * @param  string $verb      The HTTP verb used in the request (to be expanded with GET, PUT and DELETE)
 * @param  object $formstack Instance of Formstack class
 * @return string            We just return the HTML for a Bootstrap alert, for the sake of convenience
 */
function updateDataFile($verb, $formstack) {
    $dataFileContent = file_get_contents('data.json');

    if ($verb === 'POST' && isset($_POST) && !empty($_POST)) {

        $arr = json_decode($dataFileContent, true);

        $response = postSubmission($formstack);

        array_push($arr, array(
            'id' => $response[0]['id'],
            'names' => $response[0]['names'],
            'attending' => $response[0]['attending'],
            'howmany' => $response[0]['howmany'],
            ));

        file_put_contents('data.json', json_encode($arr));

    }

    echo '<div class="alert alert-warning alert-dismissible" role="alert">
  <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
  <strong>Guest added.</strong> Watch it appear below.
</div>';
}

updateDataFile($_SERVER['REQUEST_METHOD'], $FORMSTACK);
