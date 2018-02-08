# handle notifications displayed to the user via the jQuery Noty plugin
#
# @see http://needim.github.com/noty/

"use strict"
class window.NotificationHandler
  # Constructor
  #
  # will instantaneously display the loading notification, if neccessary
  #
  # @param [Boolean] loading_state should category_tree and article_table be assumed to be loading at the beginning
  constructor: ( loading_state = true )->
    @loading_noty_id = false
    @loading =
      category_tree: loading_state
    @checkDisplayLoading()

  # show / hide the loading notification according to the loading state
  #
  # if both *loading.category_tree* and *loading.article_table* are set to *false*, the loading notification will be hidden, if at least one of them is *true* it will be shown
  checkDisplayLoading: ->
    is_loading = false
    for elem,value of @loading
      if value then is_loading = true
    if is_loading && @loading_noty_id is false
      @loading_noty_id = noty
        text: lang.loading
        type: "alert"
        timeout: false
        speed: 1
        modal: true # fade background
    if !is_loading && @loading_noty_id isnt false
      $.noty.close @loading_noty_id
      @loading_noty_id = false

  # show a success box (green background color)
  #
  # @text [String] the text to be displayed
  showSuccess: (text) ->
    noty
      text: text
      type: "success"

  # show an error box (red background color)
  #
  # will be displayed for 8000 ms (that is much longer than a success box is displayed)
  #
  # @param [String] text the text to be displayed
  # @param [Integer] timeot how long should the notification be displayed
  showError: (text, timeout = 8000 ) ->
    noty
      text: text
      type: "error"
      timeout: timeout
