<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class InteractionModerationController extends Controller {
    public const STATUSES=['pending'=>'Chờ duyệt','approved'=>'Đã duyệt','rejected'=>'Từ chối'];
    public function reviews(Request $request):View{$data=$request->validate(['status'=>'nullable|in:pending,approved,rejected','search'=>'nullable|string|max:100']);$query=Review::query()->with('product:id,name');if(!empty($data['status']))$query->where('status',$data['status']);if($search=trim((string)($data['search']??'')))$query->where(fn($q)=>$q->where('name','like',"%{$search}%")->orWhere('content','like',"%{$search}%"));return view('admin.interactions.reviews',['reviews'=>$query->latest()->paginate(20)->withQueryString(),'statuses'=>self::STATUSES]);}
    public function updateReview(Request $request,Review $review):RedirectResponse{$data=$request->validate(['status'=>'required|in:pending,approved,rejected','is_verified'=>'nullable|boolean']);$review->update(['status'=>$data['status'],'is_verified'=>$request->boolean('is_verified')]);return back()->with('success','Đã cập nhật đánh giá.');}
    public function destroyReview(Review $review):RedirectResponse{$review->delete();return back()->with('success','Đã xóa đánh giá.');}
    public function comments(Request $request):View{$data=$request->validate(['status'=>'nullable|in:pending,approved,rejected','search'=>'nullable|string|max:100']);$query=Comment::query()->with('post:id,name');if(!empty($data['status']))$query->where('status',$data['status']);if($search=trim((string)($data['search']??'')))$query->where(fn($q)=>$q->where('name','like',"%{$search}%")->orWhere('content','like',"%{$search}%"));return view('admin.interactions.comments',['comments'=>$query->latest()->paginate(20)->withQueryString(),'statuses'=>self::STATUSES]);}
    public function updateComment(Request $request,Comment $comment):RedirectResponse{$comment->update($request->validate(['status'=>'required|in:pending,approved,rejected']));return back()->with('success','Đã cập nhật bình luận.');}
    public function destroyComment(Comment $comment):RedirectResponse{$comment->delete();return back()->with('success','Đã xóa bình luận.');}
}
