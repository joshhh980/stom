namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Fetch record counts cleanly using Laravel's Query Builder
        $counts = [
            'po_records'        => DB::table('purchase_order_list')->count(),
            'receiving_records' => DB::table('receiving_list')->count(),
            'sales_records'     => DB::table('sales_list')->count(),
            'suppliers'         => DB::table('supplier_list')->where('status', 1)->count(),
            'items'             => DB::table('item_list')->where('status', 1)->count(),
            'users'             => DB::table('users')->where('id', '!=', 1)->count(),
        ];

        return view('dashboard', compact('counts'));
    }
}
