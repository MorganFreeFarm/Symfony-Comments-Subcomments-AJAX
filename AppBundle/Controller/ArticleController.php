<?php
declare(strict_types = 1);
namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use AppBundle\Entity\ArticleCategory;
use AppBundle\Entity\Comment;
use AppBundle\Entity\User;
use AppBundle\Form\CommentFormType;
use AppBundle\Repository\ArticleRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ArticleController extends Controller
{
    /**
     * @Route("/articles/{category}", name="articles")
     */
    public function getArticles($category)
    {
        $em = $this->getDoctrine()->getManager();
        $categoryId = $em->getRepository('AppBundle:ArticleCategory')->getCategoryIdByCode($category);
        $articles = $em->getRepository('AppBundle:Article')->findAllByCategory($categoryId);
        return $this->render('articles/articles.html.twig', [
            'articles' => $articles
        ]);
    }

    /**
     * @Route("/articles/{category}/{id}", name="article")
     */
    public function getArticle($category, $id)
    {
        $articleRepo = $this->getDoctrine()->getRepository(Article::class);
        $article = $articleRepo->find($id);

        $categoryRepo = $this->getDoctrine()->getRepository(ArticleCategory::class);
        $categories = $categoryRepo->findAll();

        $categoryId = $categoryRepo->getCategoryIdByCode($category);
        $lastArticles = $articleRepo->getLimitArticles($categoryId, 3);

        $nextArticle = $articleRepo->getNextArticle($categoryId, $id);
        $previousArticle = $articleRepo->getPreviousArticle($categoryId, $id);

        if (is_null($nextArticle)) {
            $nextArticle = $articleRepo->getRandomArticle($categoryId, $id);
        }

        if (is_null($previousArticle)) {
            $previousArticle = $articleRepo->getRandomArticle($categoryId, $id);
        }

        $commentForm = $this->createForm(CommentFormType::class);

        return $this->render('articles/article.html.twig', [
            'article' => $article,
            'categories' => $categories,
            'lastArticles' => $lastArticles,
            'nextArticle' => $nextArticle,
            'previousArticle' => $previousArticle,
            'commentForm' => $commentForm->createView(),
        ]);
    }

    /**
     * @Route("/articles/{category}/{id}/addcomment/{replyToId}", defaults={"replyToId"=0}, name="addcomment")
     */
    public function addComment(Request $request, $category, $id, $replyToId)
    {
        $status = 'error';
        $message = '';

        $form = $this->createForm(CommentFormType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $comment->setPersonId(1);
            $comment->setReplyTo($replyToId);
            $comment->setPostId($id);
            $comment->setDateAdded(new \DateTime("now"));

            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            try {
                $em->flush();
                $status = "success";
                $message = "new comment was saved";
            } catch (\Exception $e) {
                $message = $e->getMessage();
            }
        }

        $response = array(
            'status' => $status,
            'message' => $message,
        );

        return new JsonResponse($response);
    }

    /**
     * @Route("/articles/{category}/{id}/addanswer/{replyToId}", defaults={"replyToId"=0}, name="addanswer")
     */
    public function addAnswer(Request $request, $category, $id, $replyToId)
    {
        $status = 'error';
        $message = '';

        $comment = new Comment();
        $comment->setContent($request->request->get('textarea-answer'));
        $comment->setPersonId(1);
        $comment->setReplyTo($replyToId);
        $comment->setPostId($id);
        $comment->setDateAdded(new \DateTime("now"));

        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        try {
            $em->flush();
            $status = "success";
            $message = "new answer was saved";
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }


        $response = array(
            'status' => $status,
            'message' => $message,
        );

        return new JsonResponse($response);
    }

    /**
     * @Route("/articles/{category}/{id}/true", name="articleAjax")
     */
    public function getArticlesAjax($category, $id)
    {
        $html = '';
        $articleRepo = $this->getDoctrine()->getRepository(Article::class);
        $article = $articleRepo->find($id);

        $commentRepo = $this->getDoctrine()->getRepository(Comment::class);
        $comments = $commentRepo->getComments($id);

        foreach ($comments as &$comment) {
            $subcomments = $commentRepo->getSubComments($comment['id']);
            $comment['subComments'] = $subcomments;
        }

        foreach ($comments as &$comment) {
            $html .= '
                    <div class="comment_area">
                        <div class="media">
                            <div class="media-left">
                                <a href="#">
                                    <img class="media-object" src="images/testimonial-4.jpg" alt="">
                                </a>
                            </div>
                            <div class="media-body" id="comment-div">
                                <a class="media-heading" href="#">тест</a>
                                <h5>Oct 18, 2016</h5>
                                <p>' . $comment['content'] . '</p>
                                <a class="reply" href="'. $comment['id'] . '">Отговори</a>
                            </div>
                            <div class="post_comment" id="post-comment' . $comment['id'] . '" style="display: none;">
                                <h3>Добави отговор</h3>
                            <form method="POST" class="comment_box answer_box" id="answer-form' . $comment['id'] . '" action="' . $id . '\addanswer\\' . $comment['id'] . '") }}">
                                <textarea id="textarea-answer" name="textarea-answer" class="form-control input_box"></textarea>
                                <button type="submit" id="saveButtonAnswer">Изпрати</button>
                            </form>
                            </div>
                        </div>
                    </div>';

            foreach ($comment['subComments'] as $subComment) {
                $html .= '<div class="comment_area reply_comment">
                                <div class="media">
                                    <div class="media-left">
                                        <a href="#">
                                            <img class="media-object" src="images/testimonial-1.jpg" alt="">
                                        </a>
                                    </div>
                                    <div class="media-body">
                                        <a class="media-heading" href="#">Prodip Ghosh</a>
                                        <h5>Oct 18, 2016</h5>
                                        <p>' . $subComment['content'] . '</p>
                                    </div>
                                </div>
                            </div>';
            }
        }

        $response = [
            'html' => $html
        ];

        return new JsonResponse($response);
    }
}