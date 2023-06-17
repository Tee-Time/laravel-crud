<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;
use Illuminate\Database\Connection;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    private $db;
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function create(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $this->validateArticleData($request);

        // Start a database transaction
        $this->db->beginTransaction();

        try {
            // Insert the article into the database
            $articleId = $this->createArticle($validatedData);

            // Commit the transaction
            $this->db->commit();

            // Return the ID of the newly created article
            return new JsonResponse(['articleId' => $articleId]);
        } catch (\Exception $e) {
            // Rollback the transaction if an exception occurs
            $this->db->rollback();
            echo $e;
            // Return an error response
            return new JsonResponse(['error' => 'Failed to create article'], 500);
        }
    }

    private function validateArticleData(Request $request)
    {
        return $request->validate([
            'title' => 'required|string|max:200',
            'body' => 'required|string|max:1000',
            'userId' => 'required|integer',
            'identifier' => 'required|string'
        ]);
    }
    private function getCurrentDateTime()
    {
        $currentDateTime = new DateTime();
        return $currentDateTime->format('Y-m-d H:i:s');
    }

    private function createArticle(array $data)
    {
        return $this->db->table('articles')->insertGetId([
            'title' => $data['title'],
            'body' => $data['body'],
            'user_id' => $data['userId'],
            'identifier' => $data['identifier'],
            'published_at' => $this->getCurrentDateTime()
        ]);
    }

    public function patch(Request $request, $id)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'title' => 'string',
            'body' => 'string',
        ]);

        // Fetch existing article from the database
        $existingArticle = $this->db->table('articles')->find($id);

        if (!$existingArticle) {
            return new JsonResponse(['error' => 'Article not found.'], 404);
        }

        // Merge existing and updated article data
        $updatedArticle = (array)$existingArticle;
        $updatedArticle = array_merge($updatedArticle, $validatedData);

        try {
            // Begin transaction
            $this->db->beginTransaction();

            // Update the article in the database
            $this->updateArticle($id, $updatedArticle);

            // Commit transaction
            $this->db->commit();
        } catch (\Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            return new JsonResponse(['error' => 'Failed to update article.'], 500);
        }

        return new JsonResponse(['message' => 'Article updated successfully.']);
    }

    private function updateArticle($id, $updatedArticle)
    {
        // Retrieve the existing article from the database
        $existingArticle = $this->db->table('articles')->find($id);

        if ($existingArticle) {
            // Filter only the modified fields
            $filteredData = collect($updatedArticle)->filter(function ($value, $key) use ($existingArticle) {
                return $value !== $existingArticle->{$key};
            })->except(['id']);
            // Perform the update query
            if ($filteredData->isNotEmpty()) {
                $this->db->table('articles')->where('id', $id)->update($filteredData->all());
            }
        }
    }

    // lists all the articles in the database
    public function index(Request $request)
    {
        $search = $request->input('search');

//TODO here I should also add redis
        $query = $this->db->table('articles')
            ->join('users', 'articles.user_id', '=', 'users.id')
            ->select('articles.title', 'users.email')
            ->whereNotNull('articles.published_at')
            ->orderByDesc('articles.published_at');
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('articles.title', 'like', '%' . $search . '%')
                    ->orWhere('articles.body', 'like', '%' . $search . '%');
            });
        }

        $articles = $query->get();

        return new JsonResponse($articles);
    }

    public function delete(Request $request, $id)
    {
        // Check if the article exists
        $article = $this->db->table('articles')->where('id', $id)->first();

        if (!$article) {
            return new JsonResponse(['error' => 'Article not found'], 404);
        }

        // Check if the article is published
        if ($article->published_at) {
            // Unpublish the article
            $this->db->table('articles')->where('id', $id)->update([
                'published_at' => null,
            ]);

            return new JsonResponse(['message' => 'Article unpublished'], 200);
        } else {
            // Delete the article
            $this->db->table('articles')->where('id', $id)->delete();

            return new JsonResponse(['message' => 'Article deleted'], 200);
        }
    }
}






