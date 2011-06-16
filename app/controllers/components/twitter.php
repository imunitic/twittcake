<?php
    App::import('Core', array('HttpSocket', 'Xml'));
    
    /**
     * Twitter xml api implementation
     * Documentation can be found on:
     * http://apiwiki.twitter.com/Twitter-API-Documentation
     */
    class TwitterComponent extends Object {
        var $username = '';
        var $password = '';
        var $Http = null;
        
        function __construct() {
            $this->Http =& new HttpSocket();
        }
        
        /**
         * Returns the 20 most recent statuses from non-protected users
         * who have set a custom user icon.  Does not require authentication.
         *
         * @param array params Optional. parameters passed to the query
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation
         */
        function status_public_timeline($params=array()) {
            $url = 'http://twitter.com/statuses/public_timeline.xml';
            return $this->__process($this->Http->get($url, $params));
        }

        /**
         * Returns the 20 most recent statuses posted in the last 24 hours from the authenticating
         * user and that user's friends.  It's also possible to request another user's
         * friends_timeline via the id parameter below.
         *
         * @param string id Optional. Specifies the ID or screen name of the user for whom to return the friends_timeline
         * @param array params Optional. parameters passed to the query
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation
         */
        function status_friends_timeline($id=false, $params = array()) {
            $url = 'http://twitter.com/statuses/friends_timeline';
            if($id != false) {
                $url .= "/{$id}.xml";
            } else {
                $url .= ".xml";
            }
            
            return $this->__process($this->Http->get($url, $params));
        }
        
        /**
         * Returns the 20 most recent statuses posted in the last 24 hours from the authenticating user.
         * It's also possible to request another user's timeline via the id parameter below.
         *
         * @param string id Optional. Specifies the ID or screen name of the user for whom to return the friends_timeline.
         * @param array params Optional. parameters passed to the query
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation
         */
        function status_user_timeline($id=false, $params = array()) {
            $url = 'http://twitter.com/statuses/user_timeline';
            if($id != false) {
                $url .= "/{$id}.xml";
            } else {
                $url .= ".xml";
            }
            
            return $this->__process($this->Http->get($url, $params));
        }

        /**
         * Returns the 20 most recent mentions (status containing @username) 
         * for the authenticating user.
         *
         * @param string since_id Optional.  Returns only statuses with an ID greater than (that is, more recent than) the specified ID.
         * @param string max_id Optional.Returns only statuses with an ID less than (that is, older than) or equal to the specified ID. 
         * @param int count Optional.  Specifies the number of statuses to retrieve. May not be greater than 200.
         * @param int page Optional. Specifies the page or results to retrieve. Note: there are pagination limits.
         * @see http://apiwiki.twitter.com/Twitter-REST-API-Method%3A-statuses-mentions
         */
        function status_mentions($since_id = null, $max_id=null, $count=null, $page=null) {
            $url = "http://twitter.com/statuses/mentions.xml";

            if($since_id != null) $url = $this->__addParam("since_id", $since_id, $url);
            if($max_id != null) $url = $this->__addParam("max_id", $max_id, $url);
            if($count != null) $url = $this->__addParam("count", $count, $url);
            if($page != null) $url = $this->__addParam("page", $page, $url);

            return $this->__process($this->Http->get($url, null, $this->__getAuthHeader()));
        }
        /**
         * Returns a single status, specified by the id parameter below.
         * The status's author will be returned inline.
         *
         * @param string id Required. The numerical ID of the status you're trying to retrieve.
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation
         */
        function status_show($id) {
            $url = "http://twitter.com/statuses/show/{$id}.xml";
            
            return $this->__process($this->Http->get($url));
        }
        
        /**
         * Updates the authenticating user's status.  Requires the status parameter specified below.
         *
         * @param string status Required.  The text of your status update.
         *              Be sure to URL encode as necessary.  Must not be more than 160 characters
         *              and should not be more than 140 characters to ensure optimal display.
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation             
         */
        function status_update($status) {
            $url = "http://twitter.com/statuses/update.xml";
            return $this->__process($this->Http->post($url, array('status' => $status), $this->__getAuthHeader()));
        }
        
        /**
         * Destroys the status specified by the required ID parameter.
         *
         * @param string id Required.  The ID of the status to destroy.
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation
         */
        function status_destroy($id) {
            $url = "http://twitter.com/statuses/destroy/{$id}.xml";
            return $this->__process($this->Http->get($url, null, $this->__getAuthHeader()));
        }
 
        /**
         * Returns extended information of a given user, specified by ID or screen name as per the required id parameter below.
         *
         * @param string id Required.  The ID or screen name of a user.
         * @param array params Optional. Parameters passed to the query.
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation
         */
        function user_show($id=false, $params = array()) {
            $url = "http://twitter.com/users/show";
            if($id != false) {
                $url .= "/{$id}.xml";
            } else {
                $url .= ".xml";
            }
            
            return $this->__process($this->Http->get($url, $params, $this->__getAuthHeader()));
        }
       
        /**
         * Returns up to 100 of the authenticating user's friends who have most recently updated, each with current status inline.
         * It's also possible to request another user's recent friends list via the id parameter below.
         *
         * @param string id Optional.  The ID or screen name of the user for whom to request a list of friends.
         * @param array params Optional. Parameters passed to the query
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation
         */
        function user_friends($id=false, $params = array()) {
            $url = "http://twitter.com/statuses/friends";
            if($id != false) {
                $url .= "/{$id}.xml";
            } else {
                $url .= ".xml";
            }
            
            return $this->__process($this->Http->get($url, $params, $this->__getAuthHeader()));
        }
        
        /**
         * Returns the authenticating user's followers, each with current status inline.
         *
         * @param array params Optional. Parameters passed to the query
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation
         */
        function user_followers($params = array()) {
            $url = "http://twitter.com/statuses/followers.xml";
            
            return $this->__process($this->Http->get($url, $params, $this->__getAuthHeader()));
        }
        
        /**
         * Returns a list of the 20 most recent direct messages sent to the authenticating user.
         *
         * @param array params Optional. Parameters passed to the query.
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation
         */
        function direct_messages($params = array()) {
            $url = "http://twitter.com/direct_messages.xml";
            
            return $this->__process($this->Http->get($url, $params, $this->__getAuthHeader()));
        }
        
        /**
         * Returns a list of the 20 most recent direct messages sent by the authenticating user.
         * 
         * @param array params Optional. Parameters passed to the query.
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation
         */
        function direct_messages_sent($params = array()) {
            $url = "http://twitter.com/direct_messages/sent.xml";
            
            return $this->__process($this->Http->get($url, $params, $this->__getAuthHeader()));
        }
        
        /**
         * Sends a new direct message to the specified user from the authenticating user.
         *
         * @param string user Required.  The ID or screen name of the recipient user.
         * @param string text Required.  The text of your direct message.
         *              Be sure to URL encode as necessary, and keep it under 140 characters.
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation              
         */
        function direct_messages_new($user, $text) {
            $url = "http://twitter.com/direct_messages/new.xml";
            $params = array('user' => $user, 'text' => $text);
            
            return $this->__process($this->Http->post($url, $params, $this->__getAuthHeader()));
        }
        
        /**
         * Destroys the direct message specified in the required ID parameter.
         *
         * @param string id Required.  The ID of the direct message to destroy.
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation
         */
        function direct_messages_destroy($id) {
            $url = "http://twitter.com/direct_messages/destroy/{$id}.xml";
            
            return $this->__process($this->Http->get($url, null, $this->__getAuthHeader()));
        }
        
        /**
         * Befriends the user specified in the ID parameter as the authenticating user.
         *
         * @param string id Required.  The ID or screen name of the user to befriend.
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation
         */
        function friendship_create($id) {
            $url = "http://twitter.com/friendships/create/{$id}.xml";
            
            return $this->__process($this->Http->get($url, null, $this->__getAuthHeader()));            
        }
        
        /**
         * Discontinues friendship with the user specified in the ID parameter as the authenticating user.
         *
         * @param string id Required.  The ID or screen name of the user with whom to discontinue friendship.
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation
         */
        function friendship_destroy($id) {
            $url = "http://twitter.com/friendships/destroy/{$id}.xml";
            
            return $this->__process($this->Http->get($url, null, $this->__getAuthHeader()));            
        }
        
        /**
         * Returns an HTTP 200 OK response code and a format-specific response if authentication was successful.
         *
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation
         */
        function account_verify_credentials() {
            $url = "http://twitter.com/account/verify_credentials.xml";
            
            return $this->__process($this->Http->post($url, null, $this->__getAuthHeader()));
        }
        
        /**
         * Returns the remaining number of API requests available to the requesting user 
         * before the API limit is reached for the current hour. Calls to rate_limit_status 
         * do not count against the rate limit.  If authentication credentials are provided, 
         * the rate limit status for the authenticating user is returned.  Otherwise, the rate 
         * limit status for the requester's IP address is returned.
         * 
         * @see http://apiwiki.twitter.com/Twitter-REST-API-Method%3A-account%C2%A0rate_limit_status
         */
        function account_rate_limit_status($authenticate = false) {
            $url = "http://twitter.com/account/rate_limit_status.xml";

            if($authenticate) return $this->__process($this->Http->get($url, null, $this->__getAuthHeader()));

            return $this->__process($this->Http->get($url));
        }
        /**
         * Ends the session of the authenticating user, returning a null cookie.
         *
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation
         */
        function account_end_session() {
            $url = "http://twitter.com/account/end_session";
            $this->Http->get($url, null, $this->__getAuthHeader());
        }
       
        /**
         * Sets which device Twitter delivers updates to for the authenticating user.
         * Sending none as the device parameter will disable IM or SMS updates.
         *
         * @param string device  Must be one of: sms, im, none.
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation
         */
        function account_update_delivery_device($device) {
            $url = 'http://twitter.com/account/update_delivery_device.xml';
            return $this->__process($this->Http->get($url, array('device' => $device), $this->__getAuthHeader()));
        }

        /**
         * Sets one or more hex values that control the color scheme of the authenticating user's profile page on twitter.com.
         *
         * @param array params One or more of the following parameters must be present. 
         *                     Each parameter's value must be a valid hexidecimal value, 
         *                     and may be either three or six characters (ex: #fff or #ffffff).
         *                        - profile_background_color.  Optional.
         *                        - profile_text_color.  Optional.
         *                        - profile_link_color.  Optional.
         *                        - profile_sidebar_fill_color.  Optional.
         *                        - profile_sidebar_border_color.  Optional.
         * @see http://apiwiki.twitter.com/Twitter-REST-API-Method%3A-account%C2%A0update_profile_colors
         */
        function account_update_profile_colors($params) {
            $url = "http://twitter.com/account/update_profile_colors.xml";

            return $this->__process($this->Http->post($url, $params, $this->__getAuthHeader()));
        }
        
        /**
         * Sets values that users are able to set under the "Account" tab of their settings page. 
         * Only the parameters specified will be updated.
         *
         * @param array params One or more of the following parameters must be present.  
         *                     Each parameter's value should be a string.  See the individual parameter descriptions 
         *                     below for further constraints.
         *                        - name. Optional. Maximum of 20 characters.
         *                        - email. Optional. Maximum of 40 characters. Must be a valid email address.
         *                        - url. Optional. Maximum of 100 characters. Will be prepended with "http://" if not present.
         *                        - location. Optional. Maximum of 30 characters. The contents are not 
         *                                              normalized or geocoded in any way.
         *                        - description. Optional. Maximum of 160 characters.

         * @see http://apiwiki.twitter.com/Twitter-REST-API-Method%3A-account%C2%A0update_profile
         */
        function account_update_profile($params) {
            $url = "http://twitter.com/account/update_profile.xml";

            return $this->__process($this->Http->post($url, $params, $this->__getAuthHeader()));
        }
        /**
         * Returns the 20 most recent favorite statuses for the authenticating user
         * or user specified by the ID parameter in the requested format.
         *
         * @param string id Optional.  The ID or screen name of the user for whom to request a list of favorite statuses.
         * @param array params Optional. Parameters passed to the query
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation
         */
        function favorites($id = false, $params = array()) {
            $url = "http://twitter.com/favorites";
            if($id != false) {
                $url .= "/{$id}.xml";
            } else {
                $url .= ".xml";
            }
            
            return $this->__process($this->Http->get($url, $params, $this->__getAuthHeader()));
        }
        
        /**
         * Favorites the status specified in the ID parameter as the authenticating user.
         *
         * @param string id Required.  The ID of the status to favorite.
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation
         */
        function favorites_create($id) {
            $url = "http://twitter.com/favorites/create/{$id}.xml";
            
            return $this->__process($this->Http->get($url, null, $this->__getAuthHeader()));
        }
        
        /**
         * Un-favorites the status specified in the ID parameter as the authenticating user.
         *
         * @param string id Required.  The ID of the status to un-favorite.
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation
         */ 
        function favorites_destroy($id) {
            $url = "http://twitter.com/favorites/destroy/{$id}.xml";
            
            return $this->__process($this->Http->get($url, null, $this->__getAuthHeader()));            
        }

        /**
         * Search for keyword using the twitter search API
         *
         * @param string $keyword
         * @param string $language
         * @see http://search.twitter.com/api
         */ 
        function search_keyword($keyword, $language = 'all', $rpp = '10', $since_id=null, $geocode=null, $show_user=false) {
            $url = "http://search.twitter.com/search.atom?q=$keyword&lang=$language&rpp=$rpp";

            if($since_id != null) $url = $this->__addParam("since_id", $since_id, $url);
            if($geocode != null) $url = $this->__addParam("geocode", $geocode, $url);
            if($show_user != null) $url = $this->__addParam("show_user", "true", $url);

            return $this->__process($this->Http->get($url)); 
        }
        
        /**
         * Returns the top ten topics that are currently trending on Twitter.  
         * The response includes the time of the request, the name of each trend, 
         * and the url to the Twitter Search results page for that topic.

         * @see http://apiwiki.twitter.com/Twitter-Search-API-Method%3A-trends        
         */
        function search_trends() {
            $url = "http://search.twitter.com/trends.json";

            return $this->Http->get($url);
        }

        /**
         * Returns the current top 10 trending topics on Twitter.  
         * The response includes the time of the request, the name of each trending topic, 
         * and query used on Twitter Search results page for that topic.
         * 
         * @param string exclude Optional. Setting this equal to hashtags will remove all hashtags from the trends list.
         * @see http://apiwiki.twitter.com/Twitter-Search-API-Method%3A-trends-current
         */
        function search_trends_current($exclude = null) {
            $url = "http://search.twitter.com/trends/current.json";

            if($exclude != null) $url = $this->__addParam("exclude", $exclude, $url);

            return $this->Http->get($url);
        }

        /**
         * Returns the top 20 trending topics for each hour in a given day.
         *
         * @param string date Optional. Permits specifying a start date for the report. The date should be formatted YYYY-MM-DD.
         * @param string exclude Optional. Setting this equal to hashtags will remove all hashtags from the trends list.
         * @see http://apiwiki.twitter.com/Twitter-Search-API-Method%3A-trends-daily
         */
        function search_trends_daily($date = null, $exclude = null) {
            $url = "http://search.twitter.com/trends/daily.json";

            if($exclude != null) $url = $this->__addParam("exclude", $exclude, $url);
            if($date != null) $url = $this->__addParam("date", $date, $url);

            return $this->Http->get($url);
        }
        
        /**
         * Returns the top 30 trending topics for each day in a given week.
         *
         * @param string date Optional. Permits specifying a start date for the report. The date should be formatted YYYY-MM-DD.
         * @param string exclude Optional. Setting this equal to hashtags will remove all hashtags from the trends list.
         * @see http://apiwiki.twitter.com/Twitter-Search-API-Method%3A-trends-weekly
         */
        function search_trends_weekly($date = null, $exclude = null) {
            $url = "http://search.twitter.com/trends/weekly.json";

            if($exclude != null) $url = $this->__addParam("exclude", $exclude, $url);
            if($date != null) $url = $this->__addParam("date", $date, $url);

            return $this->Http->get($url);
        }
        
        /**
         * Enables notifications for updates from the specified user to the authenticating user.
         *
         * @param string id Required.  The ID or screen name of the user to follow.
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation
         */ 
        function notifications_follow($id) {
            $url = "http://twitter.com/notifications/follow/{$id}.xml";
            
            return $this->__process($this->Http->get($url, null, $this->__getAuthHeader()));            
        }
        
        /**
         * Disables notifications for updates from the specified user to the authenticating user.
         *
         * @param string id Required.  The ID or screen name of the user to leave.
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation
         */         
        function notifications_leave($id) {
            $url = "http://twitter.com/notifications/leave/{$id}.xml";            
            return $this->__process($this->Http->get($url, null, $this->__getAuthHeader()));
        }
        
        /**
         * Blocks the user specified in the ID parameter as the authenticating user.
         * Returns the blocked user in the requested format when successful.
         *
         * @param string id The ID or screen_name of the user to block
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation
         */
        function block_create($id) {
            $url = "http://twitter.com/blocks/create/{$id}.xml";
            return $this->__process($this->Http->get($url, null, $this->__getAuthHeader()));
        }
        
        /**
         * Un-blocks the user specified in the ID parameter as the authenticating user.
         * Returns the un-blocked user in the requested format when successful. 
         *
         * @param string id The ID or screen_name of the user to block
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation
         */
        function block_destroy($id) {
            $url = "http://twitter.com/blocks/destroy/{$id}.xml";
            return $this->__process($this->Http->get($url, null, $this->__getAuthHeader()));            
        }
        
        /**
         * Returns the string "ok" in the requested format with a 200 OK HTTP status code.
         *
         * @see http://apiwiki.twitter.com/Twitter-API-Documentation
         */
        function help_test() {
            $url = 'http://twitter.com/help/test.xml';
            return $this->__process($this->Http->get($url));
        }
        
        
        // Private functions
        function __process($response) {
            $xml = new XML($response);
            return $xml->toArray();
        }
                
        function __getAuthHeader() {
            return array('auth' => array('method' => 'Basic',
                      'user' => $this->username,
                      'pass' => $this->password
                )
            );            

        }
        
        function __hasUrlQueryStrings($url) {
            return strpos($url, "?") !== false;
        }

        function __addParam($param, $value, $url) {
            if($this->__hasUrlQueryStrings($url)) {
                return $url .= "&$param=$value";
            }

            return $url .= "?$param=$value";
        }
   }
?>
