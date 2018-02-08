$ ->
  "use strict"

  document.notification_handler = new NotificationHandler()
  document.language_handler = new LanguageHandler()
  document.category_details = new CategoryDetails $("#details_main")
  document.category_tree = new CategoryTree $("#category_tree")
  category_tree = document.category_tree
  category_tree.initialize()


  # configure the legend for the jstree and the articles
  $('#help').bind 'click', (event) ->  $('#modal_help').modal 'toggle'
  $('#category-info').bind 'click', (event) ->  $('#modal_categories').modal 'toggle'

  $('#refresh').bind 'click', () -> window.location.href = window.location.href

# count how often a element (search) is contained in in array (array)
$.countOccurences = (array, search) ->
  counter = 0
  counter++ for el in array when el == search
  counter

# escape the . in a DOM id (. is used for classes, but sometimes we have it in the DOM id too, so we need escaping)
$.escapeId = (string) ->
  string.replace(/\./g,"\\.")

$.urlParam = (name) ->
  results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(window.location.href)
  res = results?[1]
  unless res then res = ""
  decodeURIComponent res
