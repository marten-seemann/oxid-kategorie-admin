# handle both the language selectors.
#
"use strict"

class window.CategoryTree

  # Constructor
  #
  # make a tree using the jQuery jstree plugin
  #
  # does not call the *initialize* function!
  #
  # @param [jQuery] dom_elem the DOM element where the tree should be created
  # @see http://www.jstree.com
  constructor: (@dom_elem) ->
    @catdetails = document.category_details
    @notifications = document.notification_handler
    @loading = @notifications.loading
    @catdetails.setCategoryTree(this)
    @dynamic_sorting = if document.config.dynamic_sorting then true else false

  # initialize the category tree
  #
  # does the whole configuration necessary for the jstree plugin
  #
  # calls *addListeners* at the end
  initialize: ->
    # initialize the jstree
    # must be placed at the bottom
    starttime = new Date().getTime()
    @loading.category_tree = true
    @notifications.checkDisplayLoading()

    jstree_plugins = [ "json_data",  "ui", "dnd", "crrm", "ui", "themes", "types", "cookies", "search" ]
    unless @dynamic_sorting then jstree_plugins.push "sort"
    jstree_options =
      types:
        valid_children: [ "root" ]
        types:
          loading:
            icon: image: "assets/jstree/themes/default/throbber.gif"
      ui: select_limit: 1
      search: show_only_matches: true
      core:
        initially_open: "#node_root"
        animation: 300
      themes: theme: 'default'
      # disable moving tree elements completely
      crrm:
        move:
          check_move: (m) =>
            # taken from https://groups.google.com/forum/?fromgroups=#!topic/jstree/KSNCv-jpNvA
            if @dynamic_sorting
              return true
            else
              if m.np[0].id is m.op[0].id then false else true
      # use the cookies plugin to save which nodes where opened the last time, but not which were selected
      cookies:
        save_opened: 'cat_jstree_open'
        save_selected: 'cat_jstree_select'
        save_loaded: 'cat_jstree_load'
      json_data:
        ajax:
          url: "ajax/categories.php"
          cache: false
          dataType: 'json'
          progressive_render: false
          data: (n) ->
            id: if n.attr then n.attr("id") else 0
          complete: =>
            @loading.category_tree = false
            @notifications.checkDisplayLoading()
            @addListenersTree()
      dnd:
        # open_timeout: 700
        drop_target: false
        drag_target: false
      sort: (a,b) -> # this plugin will be enabled if dynamic sorting is disabled
        if parseInt($(a).data('sort')) > parseInt($(b).data('sort')) then 1 else -1;
      plugins: jstree_plugins

    # if called with the GET parameter cat, then select this category directly
    # this functionality can be used to connect this tool to other ones such as the Category Master
    if $.urlParam('cat')
      $.removeCookie 'cat_jstree_select' # delete the cookie where jstree saves which node was selected. necessary because the cookie overwrites the initially_select option
      jstree_options.ui.initially_select = "#node_" + $.escapeId($.urlParam('cat'))

    # load the jstree with the previously specified options
    @dom_elem.jstree jstree_options

    endtime = new Date().getTime()
    console.log "Time to build the tree: "+(endtime-starttime)+" ms"
    @addListeners()

    # only for testing
    # @catdetails.showDetails "fad569d6659caca39bc93e98d13dd58b"


  # add listeners
  #
  #  adds lots of listeners to handle click, selection, deselecting etc. of nodes
  #
  addListenersTree: ->
    @addContextMenu()
    # $("#{@dom_elem.selector} li a").bind 'mousedown', (event) => # todo: selector ugly
    #   console.log event

    # show long category name - if category name was shortened - on hover
    @dom_elem.bind 'hover_node.jstree', (event, data) =>
      node = data.args[0]
      name_long = $(node).parent("li").data "name_long"
      return false if !!name_long && name_long.length == 0
      $(node).tooltip
        title: name_long
        placement: 'right'
      $(node).tooltip 'show'

    @dom_elem.bind 'move_node.jstree', (event, data) =>
      cat_element = data.args[0].o # the node which was dragged
      target_element = data.args[0].np # the drag target
      old_parent = data.args[0].op # the parent of the moved category BEFORE the move action

      @setNodeLoading(cat_element, true)
      if @dom_elem.is target_element then target = "root"
      else target = @getCategoryId target_element
      # if target_element.is old_parent # parent category did not change => user is only reordering, not moving!
      # unless @dynamic_sorting then console.error "this should not be possible" # by definition of dynamic sorting
      order = ( @getCategoryId $(child) for child in @getChildren target_element )
      $.ajax
        url: 'ajax/category_move.php'
        type: 'post'
        dataType: 'json'
        data:
          mode: 'move'
          cat: @getCategoryId cat_element
          target: @getCategoryId target_element
          order: order
        success: (data) =>
          @setNodeLoading(cat_element, false)
          if data is "false" or (data.move? and data.move is "false") # show error message and reload tree if moving failed
            @notifications.showError lang.error_move_category
            @reloadTree()
          else if data.move? and data.order is "false" then  @reloadTree() # moving was successful, but reordering not. just reload the tree to avoid further errors
          else # everything was successful. now update the tree accordingly
            for cat_id, sort of data.order
              @changeNodeSort @dom_elem.find($.escapeId("#node_#{cat_id}")), sort


  addListeners: ->
    # close all button: on click close *all* nodes in the tree
    $('#tree_close_all input[type="button"]').bind 'click', =>
      @dom_elem.jstree("close_all", -1)
      false

    @dom_elem.bind 'loaded.jstree refresh.jstree', (event, data) =>
      @changeNodeSort el for el in @dom_elem.find("li")
      @applyDynamicUpdating()
      # for obj in data
      #   obj.data.title = "<span class='sort'>#{obj.metadata.sort}</span>" + obj.data.title
      #   console.log obj
      # data

    @dom_elem.bind 'select_node.jstree', (event, data) =>
      elem = data.args[0]
      if @getCategoryId($(elem)) is "root" then @catdetails.showDetails false # dont show any details if the oxroot node is selected
      else @catdetails.showDetails @getCategoryId($(elem))

    @dom_elem.bind 'create.jstree', (event, data) =>
      node = data.rslt
      @setNodeLoading node.obj, true
      $.ajax
        url: 'ajax/category_move.php'
        type: 'post'
        dataType: 'json'
        data:
          mode: 'add'
          name: node.name
          cat: @getCategoryId data.args[0]
        success: (data) =>
          @setNodeLoading node.obj, false
          if not data? or data is "false"
            @reloadTree()
            @notifications.showError lang.error_add_category
          else
            node.obj.attr('id', "node_#{data.id}") # update the DOM id of the node. otherwise it could not be moved / deleted until a refresh of the whole page
            @updateNode data.id, { sort: data.sort, hidden: data.hidden, active: data.active }
            @applyDynamicUpdating()
            # now select the newly created node, so that one can directly edit the details
            # ugly. is there a way without traversing the DOM?
            $(node.obj).find("a").trigger "click"

    # search the jstree
    $('#tree_search').typeWatch
      callback: (data, elem) =>
        @loading.category_tree = true
        starttime = new Date().getTime()
        @dom_elem.jstree("search", data)
        endtime = new Date().getTime()
        console.log "Time to search the tree: "+(endtime-starttime)+" ms"
        # console.log treeelem.children("ul")
        # treeelem.children("ul").children("li").eq(-1).addClass("jstree-last")
        @showSubtree $(".jstree-search")
        @highlightCategories
        @loading.category_tree = false
      wait: 600,
      highlight: true
      captureLength: 0


  addContextMenu: ->
    contextmenu_items =
      "add":
        name: lang.contextmenu_add
        icon: "plus"
      "delete":
        name: lang.contextmenu_delete
        icon: "trash"
      "sep1": "---------"
      "quit":
        name: lang.contextmenu_quit
        icon: "remove"

    # drag context menu (is shown when dropped)
    $.contextMenu
      selector: "#{@dom_elem.selector} li a"
      build: (trigger, event) =>
        # make a copy of contextmenu_items. thus, we do not change contextmenu_items itself
        contextmenu_items_tmp = {}
        $.extend(true, contextmenu_items_tmp, contextmenu_items)
        contextmenu_items_tmp.delete.disabled = true unless @getChildren($(trigger)).length is 0 # only nodes with 0 children can be deleted
        return {
          callback: (key, options) =>
            node = options.$trigger.parent("li")
            newcat = options.$trigger.parent("li").attr('id').substr(5)
            if key is "quit" then return true
            else
              if key is "add"
                console.log node
                @dom_elem.jstree("create", node, "last", lang.tree_newnode)
              else if key is "delete"
                @setNodeLoading node, true
                $.ajax
                  url: "ajax/category_move.php"
                  type: 'post'
                  dataType: 'json'
                  data:
                    mode: 'delete'
                    cat: newcat
                  success: (data) =>
                    if data is "true" then @dom_elem.jstree("remove", node)
                    else
                      @notifications.showError lang.error_delete_category
                      @setNodeLoading node, false
          items: contextmenu_items_tmp
          }

  updateNode: (cat_id, opts) ->
    el = @dom_elem.find($.escapeId("#node_#{cat_id}"))
    @changeNodeSort(el, opts.sort) if opts.sort?
    if opts.hidden?
      if !!opts.hidden then el.addClass "category_hidden"
      else el.removeClass "category_hidden"
    if opts.active?
      if opts.active then el.removeClass "category_inactive"
      else el.addClass "category_inactive"
    if opts.title? then @dom_elem.jstree('rename_node', el, opts.title)

  changeNodeSort: (node, sort = undefined ) ->
    el = $(node).children("li a")
    if sort?
      sorttext = sort
      $(node).data("sort", sorttext)
    else sorttext = $(node).data "sort"
    if el.children(".sort").length is 0 then el.html el.html() + "<span class='sort'>" + sorttext + "</span>"
    else el.children(".sort").text sort
    if sort? then @dom_elem.jstree("sort", @dom_elem.children("ul"))

  reloadTree: ->
    console.log "reload"
    @dom_elem.jstree("refresh")

  setNodeLoading: (node, state) ->
    status = if state then "loading" else "default"
    @dom_elem.jstree("set_type", status, node.jstree("get_id"))
    # @dom_elem.jstree("_check")

  getCategoryId: (node) ->
    if node.is("a") then node = node.parent("li")
    $(node).attr('id').substr 5

  getChildren: (node) ->
    if node.is("a") then node = node.parent("li")
    node.children('ul').children('li')

  # get the parent node of a specifid node
  # getParentNode: (node) ->
  #   if node.is("a") then node = node.parent("li")
  #   node.parents("li")[0]


  # show the subtree of a found element
  #
  # default behaviour of jstree is to hide *all* non-matched elements
  # @param [jQuery] elem the tree node whose children (and children of children and so on) should be shown
  showSubtree: (elem) ->
    # correct the appearance of the jstree by adding the jstree-last CSS class to the last elements of each subtree
    # needed when manually showing / hiding nodes
    correctNode = (elem) ->
      last = elem.children("li").eq(-1)
      last.addClass("jstree-last")
      children = elem.children("li")
      correctNode($(child).children("ul:first")) for child in children

    elem.siblings("ul:first").find("li").show()
    correctNode(elem.siblings("ul:first"))

  # get the category names of all parent categories of a category
  #
  # the category itself will be included in the name listing
  # the order is as shown in the category tree, thus the first element returned is the name of topmost category
  #
  # @param [String] cat_id the OXID of the category
  # @return [Array<String>] the names of all parent categories starting with the topmost category
  getParentsNames: (cat_id) ->
    cat_id = $.escapeId cat_id
    names = []
    for node in $("#node_#{cat_id}").add($("#node_#{cat_id}").parents().filter("li"))
      names.push $.trim $(node).children("a").text()
    names

  applyDynamicUpdating: ->
    unless @dynamic_sorting then $(".sort").show()

  # deselect all selected tree elements
  deselectAll: ->
    @dom_elem.jstree "deselect_all"
