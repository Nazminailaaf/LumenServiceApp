<?php
 
namespace App\Http\Controllers;
 
use App\Models\Post;
use Illuminate\Http\Request;
 
class PostsController extends Controller
{
    /**
     * Accept header yang diterima service ini.
     */
    private const ACCEPT_TYPES = ['application/json', 'application/xml'];
 
    /**
     * Content-Type header yang diterima (untuk POST & PUT).
     */
    private const CONTENT_TYPES = ['application/json'];
 
    /* ---------------------------------------------------------------
     |  READ ALL  ->  GET /posts   (Accept)
     | --------------------------------------------------------------- */
    public function index(Request $request)
    {
        $accept = $this->negotiateAccept($request);
        if (!$accept) {
            return response('Not Acceptable!', 406);
        }
 
        $posts = Post::orderBy('id', 'DESC')->paginate(10);
 
        return $this->respond($posts->items(), $accept, 200, 'posts');
    }
 
    /* ---------------------------------------------------------------
     |  CREATE  ->  POST /posts   (Accept, Content-Type)
     | --------------------------------------------------------------- */
    public function store(Request $request)
    {
        // validasi 1: Accept header
        $accept = $this->negotiateAccept($request);
        if (!$accept) {
            return response('Not Acceptable!', 406);
        }
 
        // validasi 2: Content-Type header
        if (!$this->validContentType($request)) {
            return response('Unsupported Media Type', 415);
        }
 
        $post = Post::create($request->all());
 
        // Tantangan 1: format response mengikuti Accept header
        return $this->respond($post, $accept, 200, 'post');
    }
 
    /* ---------------------------------------------------------------
     |  READ ONE  ->  GET /post/{postId}   (Accept)
     | --------------------------------------------------------------- */
    public function show(Request $request, $id)
    {
        $accept = $this->negotiateAccept($request);
        if (!$accept) {
            return response('Not Acceptable!', 406);
        }
 
        $post = Post::find($id);
        if (!$post) {
            return $this->respond(['message' => 'Post not found'], $accept, 404, 'response');
        }
 
        return $this->respond($post, $accept, 200, 'post');
    }
 
    /* ---------------------------------------------------------------
     |  UPDATE  ->  PUT /post/{postId}   (Accept, Content-Type)
     | --------------------------------------------------------------- */
    public function update(Request $request, $id)
    {
        // validasi 1: Accept header
        $accept = $this->negotiateAccept($request);
        if (!$accept) {
            return response('Not Acceptable!', 406);
        }
 
        // validasi 2: Content-Type header (Tantangan 3)
        if (!$this->validContentType($request)) {
            return response('Unsupported Media Type', 415);
        }
 
        $post = Post::find($id);
        if (!$post) {
            return $this->respond(['message' => 'Post not found'], $accept, 404, 'response');
        }
 
        $post->update($request->all());
 
        return $this->respond($post, $accept, 200, 'post');
    }
 
    /* ---------------------------------------------------------------
     |  DELETE  ->  DELETE /post/{postId}   (Accept)
     | --------------------------------------------------------------- */
    public function destroy(Request $request, $id)
    {
        $accept = $this->negotiateAccept($request);
        if (!$accept) {
            return response('Not Acceptable!', 406);
        }
 
        $post = Post::find($id);
        if (!$post) {
            return $this->respond(['message' => 'Post not found'], $accept, 404, 'response');
        }
 
        $post->delete();
 
        return $this->respond(['message' => 'Post deleted'], $accept, 200, 'response');
    }
 
    /* ===============================================================
     |  HELPER METHODS
     | =============================================================== */
 
    /**
     * Ambil media type saja dari header (buang parameter seperti "; charset=utf-8").
     */
    private function mediaType($header)
    {
        if (!$header) {
            return null;
        }
 
        return strtolower(trim(explode(';', $header)[0]));
    }
 
    /**
     * Kembalikan Accept yang valid (json/xml), atau null kalau tidak valid.
     */
    private function negotiateAccept(Request $request)
    {
        $type = $this->mediaType($request->header('Accept'));
 
        return in_array($type, self::ACCEPT_TYPES, true) ? $type : null;
    }
 
    /**
     * Cek apakah Content-Type request valid (hanya application/json).
     */
    private function validContentType(Request $request)
    {
        $type = $this->mediaType($request->header('Content-Type'));
 
        return in_array($type, self::CONTENT_TYPES, true);
    }
 
    /**
     * Kirim response dalam format JSON atau XML sesuai Accept header.
     */
    private function respond($data, $accept, $status = 200, $rootName = 'response')
    {
        if ($accept === 'application/json') {
            return response()->json($data, $status);
        }
 
        // Ubah model / collection / array menjadi array biasa
        $data = json_decode(json_encode($data), true);
 
        $xml = new \SimpleXMLElement('<' . $rootName . '/>');
        $this->arrayToXml($data, $xml);
 
        return response($xml->asXML(), $status)
            ->header('Content-Type', 'application/xml');
    }
 
    /**
     * Konversi array (rekursif) menjadi elemen SimpleXMLElement.
     * Key numerik (daftar item) dibungkus menjadi <post>.
     */
    private function arrayToXml(array $data, \SimpleXMLElement $xml, $itemName = 'post')
    {
        foreach ($data as $key => $value) {
            $name = is_numeric($key) ? $itemName : $key;
 
            if (is_array($value)) {
                $child = $xml->addChild($name);
                $this->arrayToXml($value, $child, $itemName);
            } else {
                $xml->addChild($name, htmlspecialchars((string) $value));
            }
        }
    }
}