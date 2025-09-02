#!/bin/bash
# 遍历 1.jpg 到 17.jpg
for i in $(seq 1 17); do
  old="${i}.jpg"
  new="${i}_01_th.jpg"

  # 如果原始文件存在
  if [ -f "$old" ]; then
    # 如果新文件已存在，先删除
    if [ -f "$new" ]; then
      rm -f "$new"
      echo "已删除已存在的: $new"
    fi
    # 执行重命名
    mv "$old" "$new"
    echo "已重命名: $old → $new"
  else
    echo "文件不存在: $old"
  fi
done

