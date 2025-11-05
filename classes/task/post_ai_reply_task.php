<?php
namespace local_ai_forum_assistant\task;

defined('MOODLE_INTERNAL') || die();

class post_ai_reply_task extends \core\task\adhoc_task {

    public function execute() {
        global $DB;

        $data = $this->get_custom_data();
        $apikey = get_config('local_ai_forum_assistant', 'apikey');
        if (empty($apikey)) {
            return;
        }

        $post = $DB->get_record('forum_posts', ['id' => $data->postid]);
        $discussion = $DB->get_record('forum_discussions', ['id' => $data->discussionid]);
        $forum = $DB->get_record('forum', ['id' => $data->forumid]);

        if ($post && $discussion && $forum) {
            \local_ai_forum_assistant\event_observer::generate_and_post_ai_reply($forum, $discussion, $post, $apikey);
        }
    }
}
