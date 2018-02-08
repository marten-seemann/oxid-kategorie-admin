"use strict"

class window.CategoryDetails

  constructor: (@dom_elem) ->
    @notifications = document.notification_handler
    @dynamic_sorting = if document.config.dynamic_sorting then true else false

  # tell this class about the category tree
  #
  # @param [Object] tree an instance of the CategoryTree class
  setCategoryTree: (@tree) ->

  # show the details of a category
  #
  # @param [String] cat_id the OXID of the category to show the details from, if set to false, the category details will be hidden completely
  showDetails: (cat_id) ->
    unless cat_id then @dom_elem.html ""
    else
      @dom_elem.find('.tab-content').hide(100) # remove the detail content shown before until the new data is loaded
      $.ajax
        url: 'ajax/category_info.php'
        type: 'get'
        cache: false
        data:
          cat: cat_id
        error: ->
          @notifications.showError lang.error_load_details
        success: (data) =>
          if not data or data is "false" then @notifications.showError lang.error_load_details
          else
            @dom_elem.html data
            if @dynamic_sorting then $("#input_oxsort").parents("div.form-group").hide()
            @bindListeners()

  bindListeners: ->
    form = @dom_elem.find("form")
    form.ajaxForm
      url: 'ajax/category_info_save.php'
      type: 'post'
      dataType: 'json'
      beforeSubmit: (arr, form) ->
        form.find("input, textarea").attr('disabled', 'disabled')
      success: (data, a, b, elem) => # elem is the form element
        form.find("input, textarea").removeAttr 'disabled'
        @tree.updateNode data.cat_id, { title: $(elem).find("#input_oxtitle").val(), sort: data.sort, hidden: data.oxhidden, active: data.oxactive }

    createUploader = (el) =>
      setButtonText = (el) ->
        text = if $(el).find('img').length is 0 then lang.picture_upload_button else lang.picture_upload_button_update
        $(el).find('.qq-upload-button span').text text

      uploader = new qq.FileUploader
        element: $(el).find(".upload-add")[0]
        action: 'ajax/fileupload.php'
        debug: false
        multiple: false
        template: '<div class="qq-uploader">' +
                        '<div class="qq-upload-drop-area"><span></span></div>' +
                        '<a class="qq-upload-button btn btn-default"><i class="icon-camera"> </i> <span></span></a>' +
                        '<ul class="qq-upload-list"></ul>' +
                     '</div>'
        params:
          cat_id: $("#cat_id").val()
          role: $(el).attr('id').replace("file-uploader-", "")
        onUpload: (id, filename) => # executed just before the upload starts
          $(el).find(".btn").attr('disabled', 'disabled')
        onComplete: (id, filename, data) =>
          $(el).find('.btn').removeAttr 'disabled'
          if data.error? or data is "false" or data is false or data.path is "false" then @notifications.showError lang.error_upload_picture
          else
            $(el).find(".upload-delete").show()
            $(el).find('.upload-infos .picture-filename').html data.filename
            $(el).find('.upload-infos .picture-imagesize-width').text data.imagesize.width
            $(el).find('.upload-infos .picture-imagesize-height').text data.imagesize.height
            $(el).find('.upload-infos').show()
            $(el).find(".image").html $("<img src=\"#{data.path}\" class=\"category_picture\">")
            setButtonText el

      setButtonText el

      # add functionality to the delete button:
      # - initiate ajax request to tell the server to delete the picture
      # - hide the picture from the DOM
      # - modify the controls accordingly
      $(el).find(".upload-delete button").bind 'click', (event) =>
        elem = $(event.currentTarget).parents("div.fileuploader") # find the parent .fileuploader div (surrounding this whole picutre dialog)
        elem.find('.btn').attr('disabled', 'disabled')
        $.ajax
          url: 'ajax/fileupload.php'
          type: 'post'
          data:
            action: 'delete'
            cat_id: $("#cat_id").val()
            role: $(elem).attr('id').replace("file-uploader-", "")
          success: (data) =>
            elem.find('.btn').removeAttr 'disabled'
            if data is "true" # remove the picture preview and the delete button
              $(elem).find('.image').html ""
              $(elem).find('.upload-delete').hide()
              $(elem).find('.upload-infos').hide()
              setButtonText el
            else @notifications.showError lang.error_delete_picture


    for el in form.find("#pictures div.fileuploader")
      createUploader el
      if $.trim($(el).find(".image").html()).length is 0
        $(el).find(".upload-delete").hide() # hide delete button when no image is shown
        $(el).find(".upload-infos").hide()

