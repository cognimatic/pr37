/**
 * @file
 * Content Readability Processing
 */

(function ($, window, Drupal, _) {

  'use strict';
  /**
   * Drupal behavior to trigger ajax when CKEDITOR is changed.
   *
   * @type {{attach: Function}}
   */
  Drupal.behaviors.contentReadability = {
    attach: function(context) {

      // Drupal did not create an event that fires when CKEditor 5 is ready yet unfortunately,
      // https://www.drupal.org/project/drupal/issues/3319358
      $(document).ready(function() {

        // Only Target the default body field for now.
        // Possibly make fields configurable at a later date.
        const body = once('content_readability', 'body', context);
        for (let i = 0; i < body.length; i++) {
          // Get CKEditor 5 instance
          // https://ckeditor.com/docs/ckeditor5/latest/support/faq.html#how-to-get-the-editor-instance-object-from-the-dom-element
          const domEditableElement = document.querySelector('#edit-body-wrapper .ck-editor__editable');
          const editorInstance = domEditableElement.ckeditorInstance;

          // Now that we have an instance just double check that its state is ready.
          if(editorInstance.state === "ready"){
            // fire when the editor instance changes.
            editorInstance.model.document.on( 'change', () => {
              updateScore(editorInstance);
            });

            $('select#edit-content-readability-profiles').on('change', function () {
              // We need to update score calculation when target grade is adjusted.
              updateScore(editorInstance);
            });

          }
        }
      });

      /**
       * Process body date to update the readability score of the node.
       * @param editorInstance
       *  CKEditor5 Instance that contains the data we want to update.
       */
      function updateScore(editorInstance){
        // get the value of body field.
        let body = editorInstance.getData();
        let grade = $('select#edit-content-readability-profiles').val();

        $.post('/content-readability/update-score', {data: body, grade: grade}, function(data){
          $('.content-readability-score').html(data.contentReadabilityScore);
          $('#automatedReadabilityIndex').html(data.automatedReadabilityIndex);
          $('#averageSyllablesPerWord').html(data.averageSyllablesPerWord);
          $('#averageWordsPerSentence').html(data.averageWordsPerSentence);
          $('#colemanLiauIndex').html(data.colemanLiauIndex);
          $('#contentReadabilityScore').html(data.contentReadabilityScore);
          $('#daleChallDifficultWordCount').html(data.daleChallDifficultWordCount);
          $('#daleChallReadabilityScore').html(data.daleChallReadabilityScore);
          $('#fleschKincaidGradeLevel').html(data.fleschKincaidGradeLevel);
          $('#fleschKincaidReadingEase').html(data.fleschKincaidReadingEase);
          $('#gunningFogScore').html(data.gunningFogScore);
          $('#letterCount').html(data.letterCount);
          $('#percentageWordsWithThreeSyllables').html(data.percentageWordsWithThreeSyllables);
          $('#sentenceCount').html(data.sentenceCount);
          $('#smogIndex').html(data.smogIndex);
          $('#spacheDifficultWordCount').html(data.spacheDifficultWordCount);
          $('#spacheReadabilityScore').html(data.spacheReadabilityScore);
          $('#totalSyllables').html(data.totalSyllables);
          $('#wordCount').html(data.wordCount);
          $('#wordsWithThreeSyllables').html(data.wordsWithThreeSyllables);

        });
      }
    }
  };
})(jQuery, this, Drupal, _);
