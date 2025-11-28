<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-3">
                <h1 class="text-3xl font-bold text-gray-900">Order #{{ $order->id }}</h1>
                <span
                    class="px-3 py-1.5 rounded-full text-sm font-semibold 
                    {{ $order->status->value === 'delivered' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $order->status->value === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $order->status->value === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                    {{ $order->status->value === 'shipped' ? 'bg-blue-100 text-blue-800' : '' }}">
                    {{ ucfirst($order->status->value) }}
                </span>
            </div>
            <div class="text-sm text-gray-500">
                Placed on {{ $order->created_at->format('M d, Y at h:i A') }}
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Main Column: Order Items -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Order Items</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Product</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Price</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Qty</th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($order->products as $product)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                @if ($product->images->isNotEmpty())
                                                    <img class="h-10 w-10 rounded-md object-cover"
                                                        src="{{ asset('storage/' . $product->images->first()->image_url) }}"
                                                        alt="{{ $product->name }}">
                                                @else
                                                    <div
                                                        class="h-10 w-10 rounded-md bg-gray-100 flex items-center justify-center">
                                                        <svg class="h-6 w-6 text-gray-400" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $product->name }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        ${{ number_format($product->price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $product->pivot->quantity }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                        ${{ number_format($product->price * $product->pivot->quantity, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-500">
                                    Subtotal</td>
                                <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                                    ${{ number_format($order->total_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right text-base font-bold text-gray-900">Total
                                </td>
                                <td class="px-6 py-4 text-right text-base font-bold text-indigo-600">
                                    ${{ number_format($order->total_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Tracking Update Section -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Update Order Status</h3>
                <form method="POST" action="{{ route('admin.orders.update', $order) }}">
                    @csrf
                    @method('PATCH')
                    <div class="space-y-4">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="status" name="status"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->value }}"
                                        {{ $order->status == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status->value) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="comment" class="block text-sm font-medium text-gray-700">Comment
                                (Optional)</label>
                            <textarea id="comment" name="comment" rows="3"
                                class="mt-1 block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md"
                                placeholder="Add a note about this update..."></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Update Status
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Customer & Shipping -->
        <div class="space-y-6">

            <!-- Customer Details -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Customer Details</h3>
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 h-12 w-12">
                        <span class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100">
                            <span
                                class="text-lg font-medium leading-none text-indigo-700">{{ substr($order->user->name, 0, 1) }}</span>
                        </span>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-sm font-bold text-gray-900">{{ $order->user->name }}</h4>
                        <p class="text-sm text-gray-500">{{ $order->user->email }}</p>
                    </div>
                </div>
                <div class="border-t border-gray-200 pt-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Orders Count</span>
                        <span class="font-medium text-gray-900">{{ $order->user->orders()->count() }} orders</span>
                    </div>
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Shipping Address</h3>
                <address class="not-italic text-sm text-gray-600 space-y-1">
                    <p class="font-medium text-gray-900">{{ $order->address->address_line_1 }}</p>
                    @if ($order->address->address_line_2)
                        <p>{{ $order->address->address_line_2 }}</p>
                    @endif
                    <p>{{ $order->address->city }}, {{ $order->address->state }} {{ $order->address->postal_code }}
                    </p>
                    <p>{{ $order->address->country }}</p>
                </address>
            </div>
        </div>
    </div>
</x-admin-layout>
