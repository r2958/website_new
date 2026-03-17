import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import '../models/api_response.dart';
import '../services/api_service.dart';
import '../main.dart';

class AddressListScreen extends StatefulWidget {
  final List<Address>? addresses;
  final Address? selectedAddress;
  final bool allowSelection;

  const AddressListScreen({
    super.key,
    this.addresses,
    this.selectedAddress,
    this.allowSelection = false,
  });

  @override
  State<AddressListScreen> createState() => _AddressListScreenState();
}

class _AddressListScreenState extends State<AddressListScreen> {
  List<Address> _addresses = [];
  bool _isLoading = false;
  final ApiService _apiService = ApiService();

  @override
  void initState() {
    super.initState();
    if (widget.addresses != null && widget.addresses!.isNotEmpty) {
      _addresses = widget.addresses!;
    } else {
      _loadAddresses();
    }
  }

  Future<void> _loadAddresses() async {
    setState(() => _isLoading = true);
    try {
      // 确保 ApiService 已初始化
      await _apiService.init();
      print('AddressListScreen - ApiService 已初始化');
      
      final response = await _apiService.getAddresses();
      if (response.isSuccess && response.data != null) {
        setState(() {
          _addresses = response.data!.addresses;
        });
      } else {
        Fluttertoast.showToast(msg: response.message ?? '获取地址失败');
      }
    } catch (e) {
      print('加载地址失败: $e');
      Fluttertoast.showToast(msg: '网络错误');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _deleteAddress(Address address) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppTheme.backgroundWhite,
        shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
        title: const Text('确认删除', style: TextStyle(color: AppTheme.textPrimary)),
        content: const Text('确定要删除这个地址吗？', style: TextStyle(color: AppTheme.textSecondary)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            style: TextButton.styleFrom(foregroundColor: AppTheme.textSecondary),
            child: const Text('取消'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            style: TextButton.styleFrom(foregroundColor: AppTheme.errorColor),
            child: const Text('删除'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    try {
      final response = await _apiService.deleteAddress(address.id);
      if (response.isSuccess) {
        Fluttertoast.showToast(msg: '删除成功');
        _loadAddresses();
      } else {
        Fluttertoast.showToast(msg: response.message ?? '删除失败');
      }
    } catch (e) {
      Fluttertoast.showToast(msg: '删除失败');
    }
  }

  Future<void> _setDefaultAddress(Address address) async {
    try {
      final response = await _apiService.setDefaultAddress(address.id);
      if (response.isSuccess) {
        Fluttertoast.showToast(msg: '设置成功');
        _loadAddresses();
      } else {
        Fluttertoast.showToast(msg: response.message ?? '设置失败');
      }
    } catch (e) {
      Fluttertoast.showToast(msg: '设置失败');
    }
  }

  void _showAddAddressDialog() {
    final consigneeController = TextEditingController();
    final phoneController = TextEditingController();
    final provinceController = TextEditingController();
    final cityController = TextEditingController();
    final districtController = TextEditingController();
    final addressController = TextEditingController();
    final postcodeController = TextEditingController();
    bool isDefault = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) => Container(
          decoration: const BoxDecoration(
            color: AppTheme.backgroundWhite,
            borderRadius: BorderRadius.vertical(top: Radius.circular(0)),
          ),
          child: Padding(
            padding: EdgeInsets.only(
              bottom: MediaQuery.of(context).viewInsets.bottom,
              left: 16,
              right: 16,
              top: 16,
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        '添加收货地址',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.close),
                        onPressed: () => Navigator.pop(context),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: consigneeController,
                    decoration: const InputDecoration(
                      labelText: '收货人姓名 *',
                      border: OutlineInputBorder(borderRadius: BorderRadius.zero),
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: phoneController,
                    decoration: const InputDecoration(
                      labelText: '手机号码 *',
                      border: OutlineInputBorder(borderRadius: BorderRadius.zero),
                    ),
                    keyboardType: TextInputType.phone,
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: TextField(
                          controller: provinceController,
                          decoration: const InputDecoration(
                            labelText: '省份',
                            border: OutlineInputBorder(borderRadius: BorderRadius.zero),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: TextField(
                          controller: cityController,
                          decoration: const InputDecoration(
                            labelText: '城市',
                            border: OutlineInputBorder(borderRadius: BorderRadius.zero),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: districtController,
                    decoration: const InputDecoration(
                      labelText: '区县',
                      border: OutlineInputBorder(borderRadius: BorderRadius.zero),
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: addressController,
                    decoration: const InputDecoration(
                      labelText: '详细地址 *',
                      border: OutlineInputBorder(borderRadius: BorderRadius.zero),
                    ),
                    maxLines: 2,
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: postcodeController,
                    decoration: const InputDecoration(
                      labelText: '邮政编码',
                      border: OutlineInputBorder(borderRadius: BorderRadius.zero),
                    ),
                    keyboardType: TextInputType.number,
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Checkbox(
                        value: isDefault,
                        onChanged: (value) => setModalState(() => isDefault = value ?? false),
                        activeColor: AppTheme.primaryBlack,
                      ),
                      const Text('设为默认地址'),
                    ],
                  ),
                  const SizedBox(height: 24),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: () async {
                        if (consigneeController.text.isEmpty ||
                            phoneController.text.isEmpty ||
                            addressController.text.isEmpty) {
                          Fluttertoast.showToast(msg: '请填写必要信息');
                          return;
                        }

                        try {
                          final request = AddressRequest(
                            consignee: consigneeController.text,
                            phone: phoneController.text,
                            province: provinceController.text.isEmpty ? null : provinceController.text,
                            city: cityController.text.isEmpty ? null : cityController.text,
                            district: districtController.text.isEmpty ? null : districtController.text,
                            address: addressController.text,
                            postcode: postcodeController.text.isEmpty ? null : postcodeController.text,
                            isDefault: isDefault ? 1 : 0,
                          );
                          final result = await _apiService.addAddress(request);

                          if (result.isSuccess) {
                            Fluttertoast.showToast(msg: '地址添加成功');
                            Navigator.pop(context);
                            _loadAddresses();
                          } else {
                            Fluttertoast.showToast(msg: result.message ?? '添加失败');
                          }
                        } catch (e) {
                          Fluttertoast.showToast(msg: '添加失败: $e');
                        }
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppTheme.primaryBlack,
                        foregroundColor: AppTheme.backgroundWhite,
                        padding: const EdgeInsets.symmetric(vertical: 16),
                        shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
                      ),
                      child: const Text('保存'),
                    ),
                  ),
                  const SizedBox(height: 16),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.allowSelection ? '选择收货地址' : '收货地址'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadAddresses,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _addresses.isEmpty
              ? _buildEmptyView()
              : RefreshIndicator(
                  onRefresh: _loadAddresses,
                  color: AppTheme.primaryBlack,
                  child: ListView.builder(
                    padding: const EdgeInsets.only(bottom: 80),
                    itemCount: _addresses.length,
                    itemBuilder: (context, index) {
                      final address = _addresses[index];
                      final isSelected = widget.selectedAddress?.id == address.id;
                      return _buildAddressItem(address, isSelected);
                    },
                  ),
                ),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.all(16),
        decoration: const BoxDecoration(
          color: AppTheme.backgroundWhite,
          border: Border(top: BorderSide(color: AppTheme.borderColor)),
        ),
        child: SafeArea(
          child: SizedBox(
            width: double.infinity,
            height: 50,
            child: ElevatedButton(
              onPressed: _showAddAddressDialog,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryBlack,
                foregroundColor: AppTheme.backgroundWhite,
                shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
              ),
              child: const Text('添加新地址', style: TextStyle(fontSize: 16)),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildEmptyView() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.location_off_outlined,
            size: 64,
            color: AppTheme.textSecondary.withOpacity(0.5),
          ),
          const SizedBox(height: 16),
          const Text(
            '暂无收货地址',
            style: TextStyle(color: AppTheme.textSecondary),
          ),
          const SizedBox(height: 8),
          const Text(
            '点击下方按钮添加新地址',
            style: TextStyle(color: AppTheme.textSecondary, fontSize: 12),
          ),
        ],
      ),
    );
  }

  Widget _buildAddressItem(Address address, bool isSelected) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
      child: InkWell(
        onTap: widget.allowSelection
            ? () => Navigator.pop(context, address)
            : null,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  if (widget.allowSelection)
                    Padding(
                      padding: const EdgeInsets.only(right: 8),
                      child: Icon(
                        isSelected ? Icons.radio_button_checked : Icons.radio_button_unchecked,
                        color: isSelected ? AppTheme.primaryBlack : AppTheme.textSecondary,
                      ),
                    ),
                  Text(
                    address.consignee,
                    style: const TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.w500,
                      color: AppTheme.textPrimary,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Text(
                    address.phone,
                    style: const TextStyle(
                      fontSize: 14,
                      color: AppTheme.textSecondary,
                    ),
                  ),
                  if (address.isDefault == 1) ...[
                    const SizedBox(width: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        border: Border.all(color: AppTheme.primaryBlack),
                      ),
                      child: const Text(
                        '默认',
                        style: TextStyle(
                          fontSize: 10,
                          color: AppTheme.primaryBlack,
                        ),
                      ),
                    ),
                  ],
                ],
              ),
              const SizedBox(height: 8),
              Text(
                address.fullAddress,
                style: const TextStyle(
                  fontSize: 13,
                  color: AppTheme.textSecondary,
                ),
              ),
              if (!widget.allowSelection) ...[
                const SizedBox(height: 12),
                const Divider(height: 1),
                const SizedBox(height: 8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    if (address.isDefault != 1)
                      TextButton.icon(
                        onPressed: () => _setDefaultAddress(address),
                        icon: const Icon(Icons.check_circle_outline, size: 18),
                        label: const Text('设为默认'),
                        style: TextButton.styleFrom(
                          foregroundColor: AppTheme.textSecondary,
                          padding: const EdgeInsets.symmetric(horizontal: 12),
                        ),
                      ),
                    TextButton.icon(
                      onPressed: () => _deleteAddress(address),
                      icon: const Icon(Icons.delete_outline, size: 18),
                      label: const Text('删除'),
                      style: TextButton.styleFrom(
                        foregroundColor: AppTheme.errorColor,
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                      ),
                    ),
                  ],
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
