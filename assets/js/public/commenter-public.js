( function( $ ) {

	var ajax_url = Commenter_Data.ajax_url,
		discussion = JSON.parse( Commenter_Data.discussion ),
		$document = $( document ),
		$commenter = $( '#commenter-' + discussion.id ),

		Utils = {

			objects : [

				'Arguments', 
				'Function', 
				'String', 
				'Number', 
				'Date', 
				'RegExp', 
				'Array', 
				'Object', 
				'Null', 
				'Undefined',
				'Boolean'
			],

			set_type_checkers : function () {

				Utils.objects.forEach( function( name ) {

				    Utils[ 'is_' + name.toLowerCase() ] = function( obj ) {

				    	return toString.call( obj ) == '[object ' + name + ']';

				    }; 

				} );

			},

			is_url : function ( url ) {

				var url_regexp = new RegExp( /[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)?/gi );

				if( ! Utils.is_string( url ) )
					return false;

				if( url == '' )
					return false;
 
				if ( ! url_regexp.test( url ) )
					return false;

				return true;

			},

			is_email : function ( email ) {

				var email_regexp = new RegExp( /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/ );

				if( ! Utils.is_string( email ) )
					return false;

				if( email == '' )
					return false;
 
				if ( ! email_regexp.test( email ) )
					return false;

				return true;

			},

			html_present : function ( content ) {

			 	var regex = new RegExp( /<\/?[a-z][\s\S]*>/i );

			 	return regex.test( content );		

			},

			extend : function ( defaults, options ) {

			    var extended = {},
			    	prop;

			    for ( prop in defaults ) {

			        if ( Object.prototype.hasOwnProperty.call( defaults, prop ) ) {
			            extended[prop] = defaults[prop];
			        }

			    }

			    for ( prop in options ) {

			        if ( Object.prototype.hasOwnProperty.call( options, prop ) ) {
			            extended[prop] = options[prop];
			        }

			    }

			    return extended;

			},

			get_form_data : function ( form_identifier ) {

				var serialized_array = $( form_identifier ).serializeArray(),
					data = {};

				if( ! Utils.is_array( serialized_array ) )
					return null;

				serialized_array.forEach( function ( form_input ) {

					if( ! form_input.hasOwnProperty( 'name' ) )
						return;

					if( ! form_input.hasOwnProperty( 'value' ) )
						return;

					data[ form_input.name ] = form_input.value;

				} );

				return data;

			},

			set_form_data : function ( form_identifier, data = {} ) {

				var form_data = Utils.get_form_data( form_identifier ),
					form_data = Utils.extend( form_data, data );

				Object.keys( form_data ).forEach( function ( input_name ) {

					$( form_identifier ).find('*[name='+ input_name +']').val( form_data[ input_name ] );

				} );

			},

			get_query_string : function ( url = null ) {

				if( Utils.is_null( url ) )
					url = location.search;

			    var regex = new RegExp('[\\?&].*=[^&#]*'),
			    	query_string = url.match(regex);

			    return Utils.is_null( query_string[0] ) ? '' : query_string[0].split('?')[1];

			},

			get_cookies : function () {

				var cookies = {},
					cookies_array = document.cookie.split('; ');

				cookies_array.forEach( function ( cookie ) {

					var name_regexp = new RegExp( '([^\\s]*)=' ),
						cookie_name = cookie.match( name_regexp );

					if( ! cookie || ! Utils.is_array( cookie_name ) )
						return;

					cookies[ cookie_name[1] ] = Utils.get_cookie( cookie_name[1] );
 
				} );

				return cookies;	

			},

			get_cookie : function ( cookie_name ) {

				var regex = new RegExp( '[; ]' + cookie_name + '=([^\\s;]*)' ),
					match = ( ' ' + document.cookie ).match( regex );

				if ( cookie_name && Utils.is_array( match ) ) 
					return unescape( match[1].replace( /\+/g, ' ' ) );
				
				return '';

			},

			get_url_params : function ( url = null, name = null ) {

				var query_string = Utils.get_query_string( url ),
					params = {};

				if ( query_string ) {

					var query_strings = query_string.split('&');

					for ( var i = 0; i < query_strings.length; i++ ) {

						var param = query_strings[i].split( /=(.+)/ ),
							param_name = param[0].toLowerCase(),
							param_value = Utils.is_undefined( param[1] ) ? '' : param[1];

						if ( Utils.is_string( param_value ) ) param_value = param_value.toLowerCase();

						if ( param_name.match(/\[(\d+)?\]$/) ) {

							var key = param_name.replace(/\[(\d+)?\]/, '');
							if ( ! params[key] ) params[key] = [];

							if ( param_name.match(/\[\d+\]$/) ) {

								var index = /\[(\d+)\]/.exec(param_name)[1];
								params[key][index] = param_value;

							} else {

								params[key].push(param_value);

							}

						} else {

							if ( ! params[param_name] ) {

								params[param_name] = param_value;

							} else if ( params[param_name] && Utils.is_string( params[param_name] ) ) {

								params[param_name] = [params[param_name]];
								params[param_name].push( param_value );

							} else {

								params[param_name].push( param_value );

							}

						}

					}

					if( Utils.is_string( name ) && params.hasOwnProperty( name ) ) {

						params = params[ name ];

					}

				}

				return params;
			},

			init : function () {

				Utils.set_type_checkers()

			}

		},

	 	Commenter = {

			id 					: discussion.id,
			identifier 			: discussion.identifier,
			title 				: discussion.title,
			threads				: discussion.threads,
			sortings 			: discussion.sortings,
			current_sorting 	: discussion.current_sorting,
			current_thread 		: discussion.current_thread,
			current_page		: discussion.current_page,
			request_time 		: discussion.request_time,
			is_loading 			: discussion.is_loading,
			comments_height		: $commenter.height(),
			commenter_cookies 	: discussion.commenter_cookies,
			utils 				: Utils,


			get_discussion : function ( options = {}, callback = function () {} ) {

				var defaults = {

					ajax_load : false,
					data : {

							'action' 		: 'get_discussion',
							'post_id'		: Commenter.id,
							'cthread' 		: Commenter.current_thread,
							'cpage'			: 1,
							'csort'			: Commenter.current_sorting,
							'request_time' 	: Commenter.request_time

						}

					},
					options = Utils.extend( defaults, options ),
					data = Utils.extend( defaults.data, options.data );

				if( ! Commenter.is_loading && options.ajax_load ) {

					$.ajax( {

						url       : ajax_url,
						data      : data,
						type      : 'GET',
						dataType : 'json',
						beforeSend : function () {

							Commenter.is_loading = true;
							$commenter.trigger( 'commenter:is_loading', [ Commenter.is_loading ] );

						}

					} ).done( function ( data, status ) {

						callback( data, status, Commenter, options );
		 
					} ).fail( function ( data, status ) {

						callback( data, status, Commenter, options );

					} ).always( function () {

						Commenter.is_loading = false;
						$commenter.trigger( 'commenter:is_loading', [ Commenter.is_loading ] );

					} );

				}

				return Commenter;

			},				

			has_thread : function ( thread ) {

				if( ( Utils.is_object( thread ) || Utils.is_array( thread ) ) && thread.hasOwnProperty( 'slug' ) ) {
					return Commenter.threads.hasOwnProperty( thread.slug );
				}

				if( Utils.is_string( thread ) ) {
					thread = thread.toLowerCase();
					return Commenter.threads.hasOwnProperty( thread );
				}

				return false;

			},

			is_current_thread : function ( thread ) {

				if( ! Commenter.has_thread( thread ) )
					return false;

				thread = Commenter.get_thread( thread );

				if( Commenter.current_thread === thread.slug )
					return true;

				return false;

			},

			set_thread_page_count : function ( page_count, thread = Commenter.current_thread ) {

				var thread = Commenter.get_thread( thread );

				if( Utils.is_number( page_count ) && page_count >= 0 ) {
					thread.page_count = page_count;
				}

			},

			get_threads : function () {

				return Commenter.threads;

			},

			get_thread : function ( thread, options = {}, callback = () => {} ) {

				const { ajax_load = false } = options;

				if( Commenter.has_thread( thread ) ) {

					if( Utils.is_string( thread ) ) {
						slug  =  thread;
					} else if( ( Utils.is_object( thread ) || Utils.is_array( thread ) ) && thread.hasOwnProperty( 'slug' ) ) {
						slug = thread['slug'];
					}

					if( Commenter.threads.hasOwnProperty( slug ) ) {
						thread = Commenter.threads[ slug ];
					} else {
						thread = Commenter.get_current_thread();
					}		
					
				} else {				
					thread = Commenter.get_current_thread();
				}

				if( ! thread.is_loaded && ajax_load ) {

					$.ajax( {

						url       : ajax_url,
						data      : {

							'action' 		: 'get_thread',
							'post_id'		: thread.id,
							'cthread' 		: thread.slug,
							'cpage'			: thread.page,
							'csort'			: Commenter.current_sorting,
							'request_time' 	: Commenter.request_time

						},
						type      : 'GET',
						dataType : 'json'

					} ).done( function ( data, status ) {

						callback( data, status, thread, options );
		 
					} ).fail( function ( data, status ) {

						callback( data, status, thread, options );

					} );

				} else {

					var data = {

						status 			: 'success',
						thread 			: '',
						current_page 	: thread.current_page,
						page_count 		: thread.page_count,
						thread_data 	: thread

					};

					callback( JSON.stringify( data ), null, thread, options );

				}

				return thread;

			},

			set_current_thread : function ( thread, options = {} ) {

				const { ajax_load = true } = options;

				var current_thread = Commenter.get_current_thread().slug;

				if( Commenter.has_thread( thread ) ) {

					current_thread = Commenter.get_thread( thread, { ajax_load : ajax_load }, function ( data, status, thread ) {

						if( data.hasOwnProperty( 'status' ) && data.hasOwnProperty( 'thread' ) ) {

							if( data.status === 'success' ) {

								var status = data.status,
									thread_html = data.thread,
									loaded_thread = Commenter.threads[ thread.slug ],
									loaded_thread_identifier = loaded_thread.identifier;

								$( '#' + loaded_thread_identifier ).replaceWith( thread_html );

								loaded_thread.is_loaded = true;

								Commenter.render_loadmore_button( thread.slug );

							}

						}

					} ).slug;

				}

				Object.keys( Commenter.threads ).forEach( function ( thread ) {

					var thread = Commenter.threads[ thread ];

					if( thread.slug === current_thread ) {

						thread.is_current_thread = true;


					} else {

						thread.is_current_thread = false;

					}

				} );

				Commenter.current_thread = current_thread;
				Commenter.current_page = Commenter.get_current_thread().current_page;

				$commenter.trigger( 'commenter:set_current_thread', [ Commenter.get_current_thread() ] );

			},

			get_current_thread : function () {

				return Commenter.get_thread( Commenter.current_thread );

			},

			switch_threads_handler : function ( e ) {

				e.preventDefault();

				var $thread_tab = $( e.currentTarget ),
					thread_slug = $thread_tab.data( 'thread-slug' ),
					selected_thread = Commenter.get_thread( thread_slug ),
					$selected_thread = $( '.' + thread_slug + '-thread' );

				//$selected_thread.height( Commenter.comments_height + 'px' );

				if( Commenter.has_thread( selected_thread ) )
					Commenter.set_current_thread( selected_thread );

			},

			change_current_thread : function ( e, current_thread ) {

				Object.keys( Commenter.threads ).forEach( function ( thread ) {

					var thread = Commenter.threads[ thread ],
						thread_identifier = thread.identifier,
						thread_tab_id = '#' + thread_identifier + '-tab',
						thread_id = '#' + thread_identifier,
						$thread_tab = $( thread_tab_id ),
						$thread = $( thread_id );

					if( thread.slug !== current_thread.slug ) {
						$thread_tab.removeClass( 'current-tab' );
						$thread.removeClass( 'current-thread' );
					} else {
						$thread_tab.addClass( 'current-tab' );
						$thread.addClass( 'current-thread' );
					}

				} );

			},

			set_current_page : function ( page ) {

				var thread = Commenter.get_current_thread();

				if( Utils.is_number( page ) ) {
					Commenter.current_page = page;
					thread.current_page = page;
					Commenter.threads[ thread.slug ].current_page = page;
				}			

			},

			get_current_page : function () {

				return Commenter.current_page;

			},

			get_thread_identifier : function ( options = {} ) {

				const { thread = Commenter.get_current_thread() } = options;

				if ( Commenter.has_thread( thread ) ) 
					return Commenter.get_thread( thread ).identifier;

				if( thread == null )
					return Commenter.get_current_thread().identifier;

				if( thread.hasOwnProperty( 'identifier' ) )
					return thread[ 'identifier' ];
	 
				return null;

			},

			get_sortings : function () {

				return Commenter.sortings;

			},

			has_sorting : function ( sorting ) {

				if( ( Utils.is_object( sorting ) || Utils.is_array( sorting ) ) && sorting.hasOwnProperty( 'slug' ) ) {
					return Commenter.get_sortings().hasOwnProperty( sorting.slug );
				}

				if( Utils.is_string( sorting ) ) {
					sorting = sorting.toLowerCase();
					return Commenter.get_sortings().hasOwnProperty( sorting );
				}

				return false;				

			},

			get_sorting : function ( sorting ) {

				if( Commenter.has_sorting( sorting ) ) {

					if( Utils.is_string( sorting ) ) {
						slug  =  sorting;
					} else if( ( Utils.is_object( sorting ) || Utils.is_array( sorting ) ) && sorting.hasOwnProperty( 'slug' ) ) {
						slug = sorting['slug'];
					}

					if( Commenter.get_sortings().hasOwnProperty( slug ) ) {
						sorting = Commenter.get_sortings()[ slug ];
					} else {
						sorting = Commenter.get_current_sorting();
					}		
					
				} else {				
					sorting = Commenter.get_current_sorting();
				}

				return sorting;

			},

			get_current_sorting : function () {

				return Commenter.get_sorting( Commenter.current_sorting );

			},

			set_current_sorting : function ( sorting ) {

				var current_sorting = Commenter.get_current_sorting().slug,
					sortings = Commenter.get_sortings();

				if( ! Utils.is_string( sorting ) )
					return false;

				if( current_sorting === sorting )
					return false;

				if( Commenter.has_sorting( sorting ) )
					current_sorting = Commenter.get_sorting( sorting ).slug;

				Object.keys( sortings ).forEach( function ( sorting ) {

					var sorting = sortings[ sorting ];

					if( sorting.slug === current_sorting ) {

						sorting.is_current_sorting = true;

					} else {

						sorting.is_current_sorting = false;

					}

				} );

				Commenter.current_sorting = current_sorting;

				$commenter.trigger( 'commenter:set_current_sorting', [ Commenter.get_current_sorting() ] );

				return Commenter.get_current_sorting();

			},

			sort_discussion : function ( e, sorting ) {

				Commenter.get_discussion( {  ajax_load : true }, function ( data, status, commenter, options ) {

					if( data.hasOwnProperty( 'status' ) && data.status === 'success' ) {

						$( '#' + Commenter.identifier ).replaceWith( data.discussion );
						Commenter.render_loadmore_button( Commenter.current_thread );
						// TO DO: CHANGE HOW DATA IS PUT INTO COMMENTER
						Commenter.threads = data.discussion_data.threads;
						Commenter.current_page = 1;

					}

				} );

			},

			sort_by : function ( sorting ) {

				return Commenter.set_current_sorting( sorting );

			},

			find_comment : function ( comment, thread = null ) {

				var comment_identifier = '.comment',
					found_comment = null;

				if( Utils.is_number( comment ) || Utils.is_number( parseInt( comment ) ) ) {
					if( ! isNaN( parseInt( comment ) ) ) {
						comment_identifier += '-' + parseInt( comment );
					}					
				}

				if( Commenter.has_thread( thread ) ) {
					thread = Commenter.get_thread( thread );
					comment_identifier = '.' + thread.slug + '-thread ' + comment_identifier;
				}

				if( Utils.is_string( comment ) && ! Utils.is_number( parseInt( comment ) ) ) {
					comment_identifier = '.' + comment;
				}

				comment = $( comment_identifier );

				if( comment.length ) {
					found_comment = comment;
				}

				return found_comment;

			},

			hide_loadmore_button : function ( thread = null ) {

				var current_thread_identifier = Commenter.get_thread_identifier({ thread: thread });

				$( `#${current_thread_identifier} .comments-loadmore-container` ).addClass('hidden');

			},

			unhide_loadmore_button : function ( thread = null ) {

				var current_thread_identifier = Commenter.get_thread_identifier({ thread: thread });

				$( '#' + current_thread_identifier + ' .comments-loadmore-container' ).removeClass('hidden');		

			},

			remove_loadmore_button : function ( thread = null ) {

				var current_thread_identifier = Commenter.get_thread_identifier({ thread: thread });

				$( '#' + current_thread_identifier + ' .comments-loadmore-container' ).remove();			

			},

			render_loadmore_button : function ( thread = null ) {

				var current_thread_identifier = Commenter.get_thread_identifier({ thread: thread });

				$( '#' + current_thread_identifier + ' .thread-navigation' ).remove();

				Commenter.unhide_loadmore_button( thread );
	 
			},

			get_comments : function ( thread = Commenter.get_current_thread(), options = {}, callback = Commenter.set_comments ) {

				if( Utils.is_string( thread ) && Commenter.has_thread( thread ) ) {
					thread = Commenter.get_thread( thread );
				} else if( ( Utils.is_array( thread ) || Utils.is_object( thread ) ) && Commenter.has_thread( thread ) ) {
					thread = Commenter.get_thread( thread );
				}

				const { 

					post_id = Commenter.id, 
					page    = thread.current_page, 
					sorting = Commenter.current_sorting

				} = options;

				$.ajax( {

					url       : ajax_url,
					data      : {

						'action' 		: 'get_comments',
						'post_id'		: post_id,
						'cthread' 		: thread.slug,
						'cpage'			: page,
						'csort'			: sorting,
						'request_time' 	: Commenter.request_time

					},
					type      : 'GET',
					dataType : 'json'

				} ).done( function ( data, status ) {

					callback( data, status, thread, options );
	 
				} ).fail( function ( data, status ) {

					callback( data, status, thread, options );

				} );

				return false;		

			},

			set_comments : function ( comments, thread = Commenter.get_current_thread(), options = {} ) {

				var current_thread,
					current_page,
					thread_identifier;

				if( Commenter.has_thread( thread ) ) {
					current_thread = thread; 
					thread_identifier = current_thread.identifier;
				}

				if( Utils.is_object( options ) && ( options.hasOwnProperty( 'page' ) && Utils.is_number( options.page ) ) ) {
					current_page = options.page;
				}
 
				Commenter.append_comments( comments, '#' + thread_identifier + ' > .thread-comments' );
				Commenter.set_current_page( current_page );

				if( Commenter.current_page == current_thread.page_count ) {

					Commenter.hide_loadmore_button( current_thread.slug );

				}

			},

			load_more_comments_handler : function ( e ) {

				var $button = $( e.currentTarget ),
					page = Commenter.current_page + 1,
					thread_slug = $button.data( 'thread-slug' );

				$button.attr( 'disabled', true )
					   .addClass( 'loading' );

				Commenter.get_comments( thread_slug, { page : page }, function ( data, status, thread, options ) {

					if( data.hasOwnProperty( 'status' ) && data.hasOwnProperty( 'comments' ) ) {

						if( data.status === 'success' ) {

							var comments = data.comments,
								page = data.current_page,
								page_count = data.page_count,
								thread = thread;

							Commenter.set_thread_page_count( page_count );
							Commenter.set_comments( comments, thread, options );

							$button.attr( 'disabled', false )
			   		   			   .removeClass( 'loading' );

						}
						
					}					

				} );

			},

			append_comments : function ( comments, location = '.' + Commenter.current_thread + '-thread' ) {

				$( location ).append( comments );

			},

			switch_sortings_handler : function ( e ) {

				e.preventDefault();

				var $sorting = $( e.currentTarget ),
					sorting_slug = $sorting.data( 'sorting-slug' );

				Commenter.set_current_sorting( sorting_slug );

			},

			do_action : function ( options = {}, callback = function () {} ) {

				var defaults = { 

					method 	: 'GET',
					data 	: {

						action			: 'do_action',
						post_id			: Commenter.id,
						caction 		: null,
						cid				: 0,
						cpage   		: Commenter.current_page,
						cthread 		: Commenter.current_thread,
						csort			: Commenter.current_sorting,
						request_time 	: Commenter.request_time

					}

				},
				options = Utils.extend( defaults, options ),
				data = Utils.extend( defaults.data, options.data );

				$.ajax( {

					url       	: ajax_url,
					data      	: data,
					type      	: options.method,
					dataType 	: 'json'

				} ).done( function ( data, status ) {

					callback( data, status, options );
	 
				} ).fail( function ( data, status ) {

					callback( data, status, options );

				} );

			},

			post_comment_handler : function ( e ) {

				e.preventDefault();

				var $comment_form = $( e.currentTarget ),
					form_identifier = $comment_form.attr( 'id' ),
					form_data = Utils.get_form_data( '#' + form_identifier ),
					comment_form_notice_container = '#' + form_identifier + ' .notices-container',
					data = {

						method : 'POST',
						data : form_data

					};

				Commenter.disable_comment_forms();

				Commenter.post_comment( data, function ( data, status, options ) {

					if( data.hasOwnProperty( 'status' ) && data.status == 'success' ) {

						if( data.hasOwnProperty( 'comment' ) ) {

							$( '.comments-area' ).prepend( $( data.comment ).addClass( 'new-comment' ) );

						}

						if( form_data.caction == 'reply' ) {

							Commenter.reset_comment_form();

						}

						Commenter.clear_comment_form(); 

					} else if ( data.hasOwnProperty( 'status' ) && data.status == 'error' ) {

						Commenter.set_notice( data.message, data.status, comment_form_notice_container );

					}

					
					Commenter.enable_comment_forms();					

				} );

			},

			post_comment : function ( data = {}, callback = function () {} ) {

				var data = Utils.extend( {

					method : 'POST',
					data : {}

				}, data );

				Commenter.do_action( data, callback );

			},

			like_comment_handler : function ( e ) {

				e.preventDefault();

				var $like_link = $( e.currentTarget ),
					comment_id = $like_link.data( 'comment-id' );

				Commenter.like_comment( comment_id, function ( data, status, options ) {

					console.log( data );

					if( data.hasOwnProperty( 'status' ) && data['status'] === 'success' ) {

						console.log( data );

					}					

				} );

			},

			unlike_comment_handler : function ( e ) {

				e.preventDefault();

				var $unlike_link = $( e.currentTarget ),
					comment_id = $unlike_link.data( 'comment-id' );

				console.log( Utils.get_url_params( $unlike_link.attr( 'href' ) ) );

				$unlike_link.addClass( 'loading' );

				$unlike_link.find( '.animated-heart' )
						    .removeClass( 'activated' );

				$unlike_link.addClass( 'like-action-link' )
						    .removeClass( 'unlike-action-link' )
						    .removeClass( 'loading' );

				$unlike_link.parent( '.heart-btn' )
							.removeClass('unlike-btn')
							.addClass( 'like-btn' );

				$commenter.trigger( 'commenter:unlike_comment' );

				Commenter.decrement_like_count( comment_id );
			},

			flag_comment : function () {



			},

			unflag_comment : function () {



			},

			like_comment : function ( comment_id, callback = function () {} ) {

				var comment_id = ( Utils.is_number( comment_id ) ) ? comment_id : 0,
					comment = Commenter.find_comment( comment_id, Commenter.current_thread ),
					like_comment_identifier_class = '.like-comment-' + comment_id,
					$comment = $( comment ),
					$like_link = $comment.find( like_comment_identifier_class ),
					$like_links = $( like_comment_identifier_class );

				Object.keys( $like_links ).forEach( function ( like_link ) {

					var $like_link = $( $like_links[ like_link ] );

					$like_link.addClass( 'loading' );

					$like_link.children( '.animated-heart' )
							  .addClass( 'activated' )
							  .on( 'animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd', function( e ) {

									$( this ).removeClass( 'activated' );

							  } );

					$like_link.addClass( 'unlike-action-link' )
							  .removeClass( 'like-action-link' )
							  .removeClass( 'loading' );

					$like_link.parent( '.heart-btn' )
							  .removeClass('like-btn')
							  .addClass( 'unlike-btn' );

					$commenter.trigger( 'commenter:like_comment' );				


				} );

				var data = { 

					method	: 'GET', 
					data 	: Utils.get_url_params( $like_link.attr( 'href' ) )

				};

				Commenter.increment_like_count( comment_id );
				Commenter.do_action( data, callback );

			},

			unlike_comment : function ( comment, callback = function () {} ) {



			},

			get_permalink : function ( comment ) {

				return false;

			},

			get_comment_form : function ( thread = Commenter.current_thread ) {

				if( ! Utils.is_string( thread ) || ! Commenter.has_thread( thread ) )
					return false;

				var current_thread = Commenter.get_thread( thread ),
					thread_slug = current_thread.slug,
					post_id = Commenter.id,
					comment_form_identifier = '#' + thread_slug + '-comment-form-container-' + post_id,
					$comment_form = $( comment_form_identifier );

				if( ! $comment_form.length )
					return null;

				return $comment_form;

			},

			disable_comment_forms : function () {

				var threads = Commenter.get_threads();

				Object.keys( threads ).forEach( function( thread ) {

					Commenter.disable_comment_form( thread );

				} );

			},

			disable_comment_form : function ( thread = Commenter.current_thread ) {

				var $comment_form = $( Commenter.get_comment_form( thread ) );

				if( ! $comment_form.length )
					return false;

				$comment_form.find( ':input' ).prop( 'disabled', true );
				$comment_form.find( '.form-cancel-link' ).css( 'pointer-events', 'none' );
				$comment_form.find( '.form-cancel-link' ).addClass( 'loading' );

			},

			enable_comment_forms : function () {

				var threads = Commenter.get_threads();

				Object.keys( threads ).forEach( function( thread ) {

					Commenter.enable_comment_form( thread );

				} );

			},

			enable_comment_form : function ( thread = null ) {

				var $comment_form = $( Commenter.get_comment_form( thread ) );

				if( ! $comment_form.length )
					return false;

				$comment_form.find( ':input' ).prop( 'disabled', false );
				$comment_form.find( '.form-cancel-link' ).css( 'pointer-events', 'auto' );
				$comment_form.find( '.form-cancel-link' ).removeClass( 'loading' );

			},

			clear_comment_form : function ( $force = false ) {

				var $comment_form = $( Commenter.get_comment_form() );

				$comment_form.find( '.commenter-comment-form-textarea' ).val( '' );
				$comment_form.find( '.notices-container' ).html( '' );

				if( Utils.is_boolean( $force ) && $force ) {

					$comment_form.find( 'input.author' ).val( '' );
					$comment_form.find( 'input.email' ).val( '' );
					$comment_form.find( 'input.url' ).val( '' );

					if( $comment_form.find( '.toggle-checkbox' ).prop( 'checked' ) )
						$comment_form.find( '.toggle-checkbox' ).prop( 'checked', false );

				}

			},

			reset_comment_form : function () {

				var $comment_form = $( Commenter.get_comment_form() ),
					current_thread = Commenter.get_current_thread(),
					comment_form_id = '#' + Commenter.current_thread + '-comment-form-' + Commenter.id;

				Utils.set_form_data( comment_form_id, { cid: 0, caction: 'comment' } );

				$comment_form.prependTo( '#' + current_thread.identifier );
				$comment_form.find( '.citation .has-parent > a.parent-comment-link' ).attr( 'href', '#no-parent' );
				$comment_form.find( '.citation .has-parent > a.parent-comment-link' ).text( '' );
				$comment_form.find( '.citation .has-parent' ).addClass( 'hidden' );

				Commenter.clear_comment_form();

			},

			set_reply_comment_form : function ( parent_comment_id, options = {} ) {

				var	$comment_form = $( Commenter.get_comment_form() ),
					$parent_comment = $( Commenter.find_comment( parent_comment_id, Commenter.current_thread ) ),
					$comment_body = $parent_comment.find( '.comment-body' ).eq(0),
					comment_data = $parent_comment.data( 'comment-data' ),
					comment_identifier = Commenter.current_thread + '-' + comment_data.identifier,
					comment_form_id = '#' + Commenter.current_thread + '-comment-form-' + Commenter.id;

				if( Utils.is_null( $comment_form ) )
					return false;

				if( Utils.is_null( $parent_comment ) )
					return false;

				Utils.set_form_data( comment_form_id, { cid: comment_data.id, caction: 'reply' } );


				$comment_form.find( '.form-cancel-link' ).removeClass( 'hidden' );
				$comment_form.find( '.citation .has-parent > a.parent-comment-link' ).attr( 'href', '#' + comment_identifier );
				$comment_form.find( '.citation .has-parent > a.parent-comment-link' ).text( comment_data.author );
				$comment_form.find( '.citation .has-parent' ).removeClass( 'hidden' );
				$comment_form.insertAfter( $comment_body );
				$comment_form.find( '.commenter-comment-form-textarea' ).focus();

			},

			set_reply_form_handler : function ( e ) {

				e.preventDefault();

				var $reply_link = $( e.currentTarget ),
					comment_id = $reply_link.data('comment-id');

				Commenter.set_reply_comment_form( comment_id );

			},

			reset_comment_form_handler : function ( e ) {

				e.preventDefault();

				var $cancel_button = $( e.currentTarget );

				$cancel_button.addClass( 'hidden' );

				Commenter.reset_comment_form();

			},

			get_comment_like_count : function ( comment ) {

				var comment = Commenter.find_comment( comment ),
					like_count = 0;

				if( Utils.is_null( comment ) )
					return null;

				like_count = parseInt( comment.find( '.like-count' ).eq(0).text(), 10 );

				if( ! Utils.is_number( like_count ) || isNaN( like_count ) || Utils.is_null( like_count ) )
					return 0;

				return like_count;

			},

			set_comment_like_count : function ( comment, like_count ) {

				var like_count = parseInt( like_count, 10 ),
					like_links = $( '.like-comment-' + comment );

				if( ! Utils.is_object( like_links ) )
					return null;

				Object.keys( like_links ).forEach( function ( like_link ) {

					var $like_link = $( like_links[ like_link ] ),
						$like_count_container = $like_link.prev( '.like-count' );

					if( Utils.is_number( like_count ) && like_count > 0 ){
						$like_count_container.text( `${like_count}` );
					} else {
						$like_count_container.text( '' );
					}				

				} );			

			},

			increment_like_count : function ( comment, increment_by = 1 ) {

				var like_count = parseInt( Commenter.get_comment_like_count( comment ), 10 ),
					 increment_by = parseInt( increment_by, 10 );

				if( Utils.is_number( increment_by ) && increment_by > 0 )
					like_count += increment_by;

				Commenter.set_comment_like_count( comment, like_count );

				return like_count;
			},

			decrement_like_count : function ( comment, decrement_by = 1 ) {

				var like_count = parseInt( Commenter.get_comment_like_count( comment ), 10 ),
					decrement_by = parseInt( decrement_by, 10 );

				if( Utils.is_number( decrement_by ) && decrement_by > 0 )
					like_count -= decrement_by;

				Commenter.set_comment_like_count( comment, like_count );

				return like_count;

			},

			menu_handler : function ( e ) {

				e.preventDefault();
				e.stopPropagation();

				var $menu_trigger = $( e.currentTarget ),
					$menu = $menu_trigger.find( '.menu' );

				if( $menu.hasClass( 'open' ) ) {
					$menu.removeClass( 'open' );
				} else {
					$menu.addClass( 'open' );
				}

			},

			set_loader : function ( e, is_loading ) {

				if( is_loading ) {

					if( $commenter.hasClass( 'loading' ) )
						$commenter.addClass( 'loading' );

					$commenter.fadeTo( 0.3, 0.6 );
					$commenter.find( '.discussion-loading-container' ).removeClass('hidden');

				} else {

					if( $commenter.hasClass( 'loading' ) )
						$commenter.removeClass( 'loading' );

					$commenter.fadeTo( 0.3, 1 );
					$commenter.find( '.discussion-loading-container' ).addClass('hidden');

				}

			},

			open_menus : function ( e ) {

				var $menus = $commenter.find( '.mini-dropdown .menu' );

				if( ! $menus.hasClass( 'open' ) ) {
					$menus.addClass( 'open' );
				}

			},

			close_menus : function ( e ) {

				var $menus = $commenter.find( '.mini-dropdown .menu' );

				if( $menus.hasClass( 'open' ) ) {
					$menus.removeClass( 'open' );
				}

			},

			set_notice : function ( notice, type = 'default', location = '.discussion-notices', options = {} ) {
				var types = ['error', 'success', 'default', 'warning'],
					notice_types = { 'error' : 'danger', 'success' : 'success', 'default' : 'default', 'warning' : 'warning' },
					$location = $( location ),
					html = '';

				if( ! $location.length )
					return false;

				if( ! Utils.html_present( notice ) && ! $( notice ).hasClass( 'commenter-notice' ) ) {

					if( types.includes( type ) ) {
						html += '<div class="commenter-notice ajax alert '+ type +' alert-'+ notice_types[ type ] +'">';
						html += '<div class="commenter-notice-'+ type +'">' + notice + '</div>';
					} else {
						html += '<div class="commenter-notice ajax alert error">';
						html += '<div class="commenter-notice-error">' + notice + '</div>';
					}

					html += '</div>';

				} else {

					html = notice;

				}
 
				$location.html( html ).css( 'display', 'none' ).slideDown('fast');

				return true;
			},

			init : function () {

				Commenter.render_loadmore_button();

				$document.on( 'click', 											Commenter.close_menus 					);

				$commenter.on( 'click',  '.comments-loadmore:not(.loading)', 	Commenter.load_more_comments_handler	);
				$commenter.on( 'click',  'a.like-action-link:not(.loading)', 	Commenter.like_comment_handler			);
				$commenter.on( 'click',  'a.unlike-action-link:not(.loading)', 	Commenter.unlike_comment_handler		);
				$commenter.on( 'click',  '.thread-tabs .tab > a.tab-link', 		Commenter.switch_threads_handler		);
				$commenter.on( 'click',  '.discussion-sortings a.sorting-link',	Commenter.switch_sortings_handler		);
				$commenter.on( 'click',	 '.comment-reply-link',					Commenter.set_reply_form_handler 		);
				$commenter.on( 'click',	 '.form-cancel-link:not(.loading)',		Commenter.reset_comment_form_handler	);
				$commenter.on( 'click',  '.mini-dropdown',						Commenter.menu_handler  				);
				$commenter.on( 'submit', '.comment-form', 						Commenter.post_comment_handler			);

				$commenter.on( 'commenter:set_current_thread', 					Commenter.change_current_thread			);
				$commenter.on( 'commenter:set_current_sorting',					Commenter.sort_discussion				);
				$commenter.on( 'commenter:is_loading',							Commenter.set_loader 					);
				//$commenter.on( 'commenter:is_commenting', );
			}

		}; 

	$document.ready( function () {

		Utils.init();
		Commenter.init();

	} );

	window.Commenter = Commenter;

} )( jQuery );