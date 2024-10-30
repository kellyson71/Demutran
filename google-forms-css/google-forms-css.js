var googleFormsCSS = function(params) {

  if (typeof jQuery === 'undefined') {
    console.error('google-forms-css > jquery not found');
    return;
  }

  var formURL = params.formURL;
  if (!formURL.match('^https:\/\/docs.google.com\/forms\/.*')) {
    console.error('google-forms-css > invalid form url');
    return;
  }
  formURL = formURL.replace('viewform', 'formResponse');

  jQuery.get('google-forms-css/google-forms-css-cors.php?url=' + formURL, function(data) {

    var needle = 'var FB_PUBLIC_LOAD_DATA_ = ';
    var start = data.indexOf(needle);
    if (start === -1) {
      console.error('google-forms-css > form data not found');
      return;
    }
    var end = data.indexOf(';', start);
    var json = data.substring(start + needle.length, end);
    var formData = JSON.parse(json);

    var items = formData[1][1];
    var currentSection = 0;
    var sectionElements = [];

    items.forEach(function(item, index) {

      var title = item[1];
      var description = item[2];
      var type;
      switch(item[3]) {
        case 0: type = 'text'; break;
        case 1: type = 'textarea'; break;
        case 2: type = 'radio'; break;
        case 3: type = 'select'; break;
        case 4: type = 'checkbox'; break;
        case 6: type = 'section'; break;
        case 9: type = 'date'; break;
        case 10: type = 'time'; break;
        default: return;
      }

      if (type !== 'section') {
        var name = item[4][0][0];
        var required = item[4][0][2] ? true : false;
      }

      if (type === 'section') {
        currentSection++;
        var section = jQuery('<div class="section" id="section-' + currentSection + '"></div>');
        if (title) {
          section.append('<h2>' + title + '</h2>');
        }
        if (description) {
          section.append('<p>' + description + '</p>');
        }
        jQuery('#google-forms-css-form').append(section);
        sectionElements.push(section);
        return;
      }

      var group = jQuery('<div class="form-group"></div>');

      if (title || description) {
        var labelEl = jQuery('<label></label>');
        if (type !== 'checkbox' && type !== 'radio') {
          labelEl.attr('for', 'google-forms-css-' + name);
        }

        if (title) {
          var titleEl = jQuery('<div></div>');
          titleEl.text(title);
          if (required) {
            titleEl.append(' <span class="text-danger">*</span>');
          }
          labelEl.append(titleEl);
        }

        if (description) {
          var descriptionEl = jQuery('<small class="text-muted"></small>');
          descriptionEl.text(description);
          labelEl.append(descriptionEl);
        }

        group.append(labelEl);
      }

      if (type === 'checkbox' || type === 'radio') {
        var options = item[4][0][1];
        options.forEach(function(option, index) {
          var id = 'google-forms-css-' + name + '-' + index;
          var check = jQuery('<div class="form-check"></div>');
          var input = jQuery('<input class="form-check-input">').attr({
            id: id,
            name: 'entry.' + name,
            required: required,
            type: type,
            value: option[0]
          });

          check.append(input);

          var checkLabel = jQuery('<label class="form-check-label"></label>').attr('for', id).text(option[0]);
          check.append(checkLabel);

          if (option[0] === '') {
            var otherInput = jQuery('<input class="form-control" type="text">').attr({
              id: name + '-other',
              name: 'entry.' + name + '.other_option_response'
            }).hide();
            check.append(otherInput);
          }

          input.change(function() {
            var other = $(this).parent().find('input[type="text"]');
            if ($(this).val() === '__other_option__') {
              other.show().attr('required', true);
            } else {
              other.hide().attr('required', false);
            }
          });

          group.append(check);
        });
      } else if (type === 'select') {
        var select = jQuery('<select class="form-control"></select>').attr({
          id: 'google-forms-css-' + name,
          name: 'entry.' + name,
          required: required
        });

        select.append('<option value="">Choose</option>');

        var options = item[4][0][1];
        options.forEach(function(option) {
          var opt = jQuery('<option></option>').text(option[0]);
          select.append(opt);
        });

        group.append(select);
      } else {
        var input = (type === 'textarea') ? jQuery('<textarea class="form-control" rows="3"></textarea>') : jQuery('<input class="form-control">').attr('type', type);
        input.attr({
          id: 'google-forms-css-' + name,
          name: 'entry.' + name,
          placeholder: params.placeholderText,
          required: required
        });

        group.append(input);
      }

      sectionElements[currentSection - 1].append(group);

    });

    jQuery('#google-forms-css-form').append('<div class="form-group"><button class="btn btn-primary" type="submit">Submit</button></div>');
    jQuery('#google-forms-css-loading').hide();
    jQuery('#google-forms-css-main').show();

    sectionElements[0].show();
    var currentSectionIndex = 0;

    function showSection(index) {
      sectionElements[currentSectionIndex].hide();
      sectionElements[index].show();
      currentSectionIndex = index;
    }

    sectionElements.forEach(function(section, index) {
      if (index < sectionElements.length - 1) {
        var nextButton = jQuery('<button class="btn btn-secondary" type="button">Next</button>');
        nextButton.click(function() {
          showSection(index + 1);
        });
        section.append(nextButton);
      }

      if (index > 0) {
        var prevButton = jQuery('<button class="btn btn-secondary" type="button">Previous</button>');
        prevButton.click(function() {
          showSection(index - 1);
        });
        section.append(prevButton);
      }
    });

  });

  jQuery('#google-forms-css-form').on('submit', function(e) {
    e.preventDefault();

    jQuery.ajax({
      url: formURL,
      data: jQuery(this).serialize(),
    }).always(function() {
      console.warn('google-forms-css > don\'t worry, \'failed to load\' is expected');
      jQuery('#google-forms-css-main').hide();
      jQuery('#google-forms-css-confirmation').show();
    });

    jQuery('#google-forms-css-form input, #google-forms-css-form select, #google-forms-css-form textarea').attr('disabled', true);
  });

}
