<?php


class Posts extends Controller
{
    public $postModel;
    public $tagModel;

    /**
     * Page constructor.
     */
    public function __construct()
    {
        $this->postModel = $this->model('Post');
        $this->tagModel = $this->model('Tag');
    }

    public function index() {
        $posts = $this->postModel->getPosts();
        foreach ($posts as $post) {
            $post->tags = $this ->postModel->getPostTags($post->postId);
        }
        $data = array(
            'posts' => $posts
        );
        $this->view('posts/index', $data);
    }

    public function show($id){
        $post = $this->postModel->getPostById($id);
        $post->tags = $this ->postModel->getPostTags($id);
        $data = array(
            'post' => $post
        );
        $this->view('posts/show', $data);
    }

    public function edit($id){
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $tags = $this->tagModel->getTags();
            $data = array(
                'id' => $id,
                'title' => trim($_POST['title']),
                'content' => trim($_POST['content']),
                'user_id' => $_SESSION['user_id'],
                'tags' => $tags,
                'tag_ids' => $_POST['tags'],
                'title_err' => '',
                'content_err' => ''
            );

            if(empty($data['title'])){
                $data['title_err'] = 'Please enter title';
            }
            if(empty($data['content'])){
                $data['content_err'] = 'Please enter body text';
            }

            if(empty($data['title_err']) and empty($data['content_err'])){
                $result = $this->postModel->editPost($data);
                if($result){
                    $this->postModel->removeAllTag2Post($id);
                    /*
                    foreach ($data['tag_ids'] as $tag) {
                        $this->postModel->removeTag2Post(
                            array(
                                'tag_id' => $tag,
                                'post_id' => $id
                            )
                        );
                    }*/
                    foreach ($data['tag_ids'] as $tag) {
                        $this->postModel->addTag2Post(
                            array(
                                'tag_id' => $tag,
                                'post_id' => $id
                            )
                        );
                    }
                    message('post_message', 'Post Updated');
                    redirect('posts');
                } else {
                    die('Something went wrong');
                }
            } else {
                $this->view('posts/edit', $data);
            }
        } else {
            $tags = $this->tagModel->getTags();
            $post = $this->postModel->getPostById($id);
            $postTags = $this ->postModel->getPostTags($id);


            if($post->user_id !== $_SESSION['user_id']){
                redirect('posts');
            }
            $data = array(
                'id' => $id,
                'title' => $post->post_title,
                'content' => $post->post_content,
                'tags' => $tags,
                'postTags' => $postTags
            );
            $this->view('posts/edit', $data);
        }
    }

    public function delete($id){
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $post = $this->postModel->getPostById($id);
            if($post->user_id !== $_SESSION['user_id']){
                redirect('posts');
            }
            if($this->postModel->deletePost($id)){
                message('post_message', 'Post Removed');
                redirect('posts');
            } else {
                die('Something went wrong');
            }
        } else {
            redirect('posts');
        }
    }

    public function add(){
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $tags = $this->tagModel->getTags();
            $thisPostTags =
            $data = array(
                'title' => trim($_POST['title']),
                'content' => trim($_POST['content']),
                'user_id' => $_SESSION['user_id'],
                'tags' => $tags,
                'tag_ids' => $_POST['tags'],
                'title_err' => '',
                'content_err' => ''
            );

            if(empty($data['title'])){
                $data['title_err'] = 'Please enter title';
            }
            if(empty($data['content'])){
                $data['content_err'] = 'Please enter content text';
            }

            if(empty($data['title_err']) && empty($data['content_err'])){
                $result = $this->postModel->addPost($data);
                if($result){
                    foreach ($data['tag_ids'] as $tag) {
                        $this->postModel->addTag2Post(
                          array(
                              'tag_id' => $tag,
                              'post_id' => $result
                          )
                        );
                    }
                    message('post_message', 'Post Added');
                    redirect('posts');
                } else {
                    die('Something went wrong');
                }
            } else {
                $this->view('posts/add', $data);
            }
        } else {
            $tags = $this->tagModel->getTags();
            $data = array(
                'title' => '',
                'content' => '',
                'tags' => $tags
            );
            $this->view('posts/add', $data);
        }
    }

}