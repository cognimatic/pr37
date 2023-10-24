/**
 * @file
 * Content Readability Processing for CKEditor 4.
 */

(function ($, window, Drupal, drupalSettings, _) {

  'use strict';
  /**
   * Drupal behavior to trigger ajax when CKEDITOR is changed.
   *
   * @type {{attach: Function}}
   */
  Drupal.behaviors.contentReadability = {
    attach: function(context, settings) {
      const body = once('content_readability', 'body', context);
      for (let i = 0; i < body.length; i++) {
        if (typeof CKEDITOR !== "undefined") {
          CKEDITOR.on('instanceReady', function (ev) {
            var editor = ev.editor;

            // fire when the editor instance changes.
            editor.on('change', function () {
              // Temp Fix to only apply to body fields..
              // Ideally want to pass name to change, but this needs a larger
              // rework, right now just need this to function.
              if(editor.name  == "edit-body-0-value"){
                updateScore();
              }
            });
            $('select#edit-content-readability-profiles').on('change',function(){
              // See note above
              if(editor.name  == "edit-body-0-value"){
                updateScore();
              }

            });

          });
        }
      }

      function updateScore(){


        // get the value of body field.
        let body = CKEDITOR.instances['edit-body-0-value'].getData()
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
})(jQuery, this, Drupal, drupalSettings, _);
